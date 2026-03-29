<?php

namespace App\Services;

use App\Models\RecurrenceSeries;
use App\Models\User;
use App\Repositories\Contracts\RecurrenceSeriesRepositoryInterface;
use Carbon\Carbon;

class RecurrenceSeriesService
{
    public function __construct(
        private RecurrenceSeriesRepositoryInterface $series,
        private RecurrenceMaterializationService $materialization,
    ) {}

    public function listForUser(User $user): \Illuminate\Support\Collection
    {
        return $this->series->allForUser($user->id);
    }

    public function listActiveForUser(User $user): \Illuminate\Support\Collection
    {
        return $this->series->allActiveForUser($user->id);
    }

    public function create(User $user, array $data): RecurrenceSeries
    {
        $data['user_id'] = $user->id;
        $data['is_active'] = $data['is_active'] ?? true;

        $model = $this->series->create($data);
        $this->materialization->syncSeriesWindow($model, Carbon::today(), Carbon::today()->addYear());

        return $model;
    }

    public function update(User $user, string $id, array $data): RecurrenceSeries
    {
        $model = $this->series->findForUserOrFail($user->id, $id);
        $this->materialization->cleanupFutureScheduledForSeries($model, Carbon::today());

        $updated = $this->series->update($model, $data);
        $this->materialization->syncSeriesWindow($updated, Carbon::today(), Carbon::today()->addYear());

        return $updated;
    }

    public function delete(User $user, string $id): void
    {
        $model = $this->series->findForUserOrFail($user->id, $id);
        $this->materialization->cleanupFutureScheduledForSeries($model, Carbon::today());
        $this->series->delete($model);
    }

    public function find(User $user, string $id): RecurrenceSeries
    {
        return $this->series->findForUserOrFail($user->id, $id);
    }
}
