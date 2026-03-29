<?php

namespace App\Repositories\Contracts;

use App\Models\RecurrenceSeries;
use Illuminate\Support\Collection;

interface RecurrenceSeriesRepositoryInterface
{
    public function allForUser(string $userId): Collection;

    public function allActiveForUser(string $userId): Collection;

    public function findForUserOrFail(string $userId, string $id): RecurrenceSeries;

    public function create(array $data): RecurrenceSeries;

    public function update(RecurrenceSeries $series, array $data): RecurrenceSeries;

    public function delete(RecurrenceSeries $series): void;
}
