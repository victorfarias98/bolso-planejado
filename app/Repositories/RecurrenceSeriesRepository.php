<?php

namespace App\Repositories;

use App\Models\RecurrenceSeries;
use App\Repositories\Contracts\RecurrenceSeriesRepositoryInterface;
use Illuminate\Support\Collection;

class RecurrenceSeriesRepository implements RecurrenceSeriesRepositoryInterface
{
    public function allForUser(string $userId): Collection
    {
        return RecurrenceSeries::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function allActiveForUser(string $userId): Collection
    {
        return RecurrenceSeries::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get();
    }

    public function findForUserOrFail(string $userId, string $id): RecurrenceSeries
    {
        return RecurrenceSeries::query()
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();
    }

    public function create(array $data): RecurrenceSeries
    {
        return RecurrenceSeries::query()->create($data);
    }

    public function update(RecurrenceSeries $series, array $data): RecurrenceSeries
    {
        $series->update($data);

        return $series->fresh();
    }

    public function delete(RecurrenceSeries $series): void
    {
        $series->delete();
    }
}
