<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\RecurrenceSeries;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class RecurrenceMaterializationService
{
    public function syncForUser(User $user, CarbonInterface $from, CarbonInterface $to): void
    {
        $start = Carbon::parse($from->toDateString());
        $end = Carbon::parse($to->toDateString());
        if ($end->lt($start)) {
            return;
        }

        $seriesList = RecurrenceSeries::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($seriesList as $series) {
            $this->syncSeriesWindow($series, $start, $end);
        }
    }

    public function syncSeriesWindow(RecurrenceSeries $series, CarbonInterface $from, CarbonInterface $to): void
    {
        $start = Carbon::parse($from->toDateString());
        $end = Carbon::parse($to->toDateString());
        if ($end->lt($start)) {
            return;
        }

        if (! $series->is_active) {
            $this->cleanupFutureScheduledForSeries($series, $start);
            return;
        }

        $dates = $this->monthlyOccurrencesInRange($series, $start, $end);
        $allowed = $dates->map(fn (Carbon $d) => $d->toDateString())->all();

        Transaction::query()
            ->where('user_id', $series->user_id)
            ->where('recurrence_series_id', $series->id)
            ->where('status', TransactionStatus::Scheduled->value)
            ->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])
            ->whereNotIn('occurred_on', $allowed)
            ->delete();

        foreach ($dates as $date) {
            $this->upsertOccurrence($series, $date);
        }
    }

    public function cleanupFutureScheduledForSeries(RecurrenceSeries $series, CarbonInterface $from): void
    {
        Transaction::query()
            ->where('user_id', $series->user_id)
            ->where('recurrence_series_id', $series->id)
            ->where('status', TransactionStatus::Scheduled->value)
            ->whereDate('occurred_on', '>=', $from->toDateString())
            ->delete();
    }

    /**
     * Gera ocorrências mensais de forma eficiente (sem iterar dia a dia).
     *
     * @return Collection<int, Carbon>
     */
    private function monthlyOccurrencesInRange(RecurrenceSeries $series, Carbon $from, Carbon $to): Collection
    {
        $dates = collect();

        $cursor = $from->copy()->startOfMonth();
        $endMonth = $to->copy()->startOfMonth();

        $seen = 0;
        while ($cursor->lte($endMonth)) {
            $occDay = min((int) $series->day_of_month, $cursor->daysInMonth);
            $occ = $cursor->copy()->day($occDay)->startOfDay();

            // Respeita janela solicitada.
            if ($occ->toDateString() < $from->toDateString()) {
                $cursor->addMonth();
                continue;
            }

            if ($occ->toDateString() > $to->toDateString()) {
                break;
            }

            // Respeita limites do próprio recurrence.
            if ($occ->toDateString() < $series->start_on->toDateString()) {
                $cursor->addMonth();
                continue;
            }

            if ($series->end_on && $occ->toDateString() > $series->end_on->toDateString()) {
                $cursor->addMonth();
                continue;
            }

            if ($series->max_occurrences !== null && $seen >= (int) $series->max_occurrences) {
                break;
            }

            $dates->push($occ);
            $seen++;

            $cursor->addMonth();
        }

        return $dates;
    }

    private function upsertOccurrence(RecurrenceSeries $series, Carbon $date): void
    {
        $data = [
            'user_id' => $series->user_id,
            'financial_account_id' => $series->financial_account_id,
            'category_id' => $series->category_id,
            'recurrence_series_id' => $series->id,
            'type' => $series->type->value,
            'amount' => $series->amount,
            'occurred_on' => $date->toDateString(),
            'status' => TransactionStatus::Scheduled->value,
            'description' => $series->description,
        ];

        $existing = Transaction::query()
            ->where('user_id', $series->user_id)
            ->where('recurrence_series_id', $series->id)
            ->whereDate('occurred_on', $date->toDateString())
            ->where('status', TransactionStatus::Scheduled->value)
            ->first();

        if ($existing) {
            $existing->update($data);
            return;
        }

        Transaction::query()->create($data);
    }

    // (Antigos métodos de ocorrência mensal foram removidos por desempenho.)
}
