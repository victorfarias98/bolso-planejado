<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDebtRequest;
use App\Http\Requests\Api\V1\UpdateDebtRequest;
use App\Http\Resources\Api\V1\DebtResource;
use App\Services\DebtService;
use App\Services\RecommendationCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebtController extends Controller
{
    public function __construct(
        private DebtService $debts,
        private RecommendationCacheService $recommendationCache,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $list = $this->debts->listForUser($request->user());
        $totals = $this->debts->totalsForUser($request->user());

        return DebtResource::collection($list)
            ->additional([
                'meta' => [
                    'totals' => $totals,
                ],
            ])
            ->response();
    }

    public function store(StoreDebtRequest $request): JsonResponse
    {
        $debt = $this->debts->create($request->user(), $request->validated());
        $debt->load(['financialAccount', 'category', 'recurrenceSeries']);
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return (new DebtResource($debt))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, string $debt): JsonResponse
    {
        $model = $this->debts->find($request->user(), $debt);
        $model->load(['financialAccount', 'category', 'recurrenceSeries']);

        return (new DebtResource($model))->response();
    }

    public function update(UpdateDebtRequest $request, string $debt): JsonResponse
    {
        $model = $this->debts->update($request->user(), $debt, $request->validated());
        $model->load(['financialAccount', 'category', 'recurrenceSeries']);
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return (new DebtResource($model))->response();
    }

    public function destroy(Request $request, string $debt): JsonResponse
    {
        $this->debts->delete($request->user(), $debt);
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
