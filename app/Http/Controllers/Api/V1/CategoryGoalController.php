<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCategoryGoalRequest;
use App\Http\Requests\Api\V1\UpdateCategoryGoalRequest;
use App\Http\Resources\Api\V1\CategoryGoalResource;
use App\Models\CategoryGoal;
use App\Services\RecommendationCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryGoalController extends Controller
{
    public function __construct(
        private RecommendationCacheService $recommendationCache,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $list = CategoryGoal::query()
            ->where('user_id', $request->user()->id)
            ->with('category')
            ->orderByDesc('created_at')
            ->get();

        return CategoryGoalResource::collection($list)->response();
    }

    public function store(StoreCategoryGoalRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        $model = CategoryGoal::query()->updateOrCreate(
            ['user_id' => $request->user()->id, 'category_id' => $data['category_id']],
            $data
        );
        $model->load('category');
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return (new CategoryGoalResource($model))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateCategoryGoalRequest $request, string $categoryGoal): JsonResponse
    {
        $model = CategoryGoal::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $categoryGoal)
            ->firstOrFail();

        $model->update($request->validated());
        $model->load('category');
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return (new CategoryGoalResource($model))->response();
    }

    public function destroy(Request $request, string $categoryGoal): JsonResponse
    {
        $model = CategoryGoal::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $categoryGoal)
            ->firstOrFail();
        $model->delete();
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
