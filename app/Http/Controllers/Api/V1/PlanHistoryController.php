<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePlanHistoryRequest;
use App\Http\Resources\Api\V1\PlanHistoryResource;
use App\Models\PlanHistory;
use App\Services\RecommendationCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlanHistoryController extends Controller
{
    public function __construct(
        private RecommendationCacheService $recommendationCache,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $list = PlanHistory::query()
            ->where('user_id', $request->user()->id)
            ->with('category')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return PlanHistoryResource::collection($list)->response();
    }

    public function store(StorePlanHistoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $model = PlanHistory::query()->create($data);
        $model->load('category');
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return (new PlanHistoryResource($model))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
