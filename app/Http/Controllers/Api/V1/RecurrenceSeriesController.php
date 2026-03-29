<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRecurrenceSeriesRequest;
use App\Http\Requests\Api\V1\UpdateRecurrenceSeriesRequest;
use App\Http\Resources\Api\V1\RecurrenceSeriesResource;
use App\Services\RecurrenceSeriesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecurrenceSeriesController extends Controller
{
    public function __construct(
        private RecurrenceSeriesService $series,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $list = $this->series->listForUser($request->user());
        $list->load(['financialAccount', 'category', 'debtAgreement']);

        return RecurrenceSeriesResource::collection($list)->response();
    }

    public function store(StoreRecurrenceSeriesRequest $request): JsonResponse
    {
        $model = $this->series->create($request->user(), $request->validated());
        $model->load(['financialAccount', 'category', 'debtAgreement']);

        return (new RecurrenceSeriesResource($model))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, string $recurrenceSeries): JsonResponse
    {
        $model = $this->series->find($request->user(), $recurrenceSeries);
        $model->load(['financialAccount', 'category', 'debtAgreement']);

        return (new RecurrenceSeriesResource($model))->response();
    }

    public function update(UpdateRecurrenceSeriesRequest $request, string $recurrenceSeries): JsonResponse
    {
        $model = $this->series->update($request->user(), $recurrenceSeries, $request->validated());
        $model->load(['financialAccount', 'category', 'debtAgreement']);

        return (new RecurrenceSeriesResource($model))->response();
    }

    public function destroy(Request $request, string $recurrenceSeries): JsonResponse
    {
        $this->series->delete($request->user(), $recurrenceSeries);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
