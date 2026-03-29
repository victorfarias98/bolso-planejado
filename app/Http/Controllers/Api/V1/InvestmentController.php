<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreInvestmentRequest;
use App\Http\Requests\Api\V1\UpdateInvestmentRequest;
use App\Http\Resources\Api\V1\InvestmentResource;
use App\Services\InvestmentService;
use App\Services\RecommendationCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvestmentController extends Controller
{
    public function __construct(
        private InvestmentService $investments,
        private RecommendationCacheService $recommendationCache,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $list = $this->investments->listForUser($request->user());
        $meta = $this->investments->metaForUser($request->user());

        return InvestmentResource::collection($list)
            ->additional(['meta' => $meta])
            ->response();
    }

    public function store(StoreInvestmentRequest $request): JsonResponse
    {
        $model = $this->investments->create($request->user(), $request->validated());
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return (new InvestmentResource($model))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, string $investment): JsonResponse
    {
        $model = $this->investments->find($request->user(), $investment);

        return (new InvestmentResource($model))->response();
    }

    public function update(UpdateInvestmentRequest $request, string $investment): JsonResponse
    {
        $model = $this->investments->update($request->user(), $investment, $request->validated());
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return (new InvestmentResource($model))->response();
    }

    public function destroy(Request $request, string $investment): JsonResponse
    {
        $this->investments->delete($request->user(), $investment);
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
