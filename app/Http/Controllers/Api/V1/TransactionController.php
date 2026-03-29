<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTransactionRequest;
use App\Http\Requests\Api\V1\UpdateTransactionRequest;
use App\Http\Resources\Api\V1\TransactionResource;
use App\Services\RecommendationCacheService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactions,
        private RecommendationCacheService $recommendationCache,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->transactions->paginate(
            $request->user(),
            $request->only(['financial_account_id', 'status', 'from', 'to', 'sort', 'direction']),
            (int) $request->query('per_page', 15)
        );

        return TransactionResource::collection($paginator)->response();
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactions->create($request->user(), $request->validated());
        $transaction->load(['financialAccount', 'category']);
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, string $transaction): JsonResponse
    {
        $model = $this->transactions->find($request->user(), $transaction);
        $model->load(['financialAccount', 'category']);

        return (new TransactionResource($model))->response();
    }

    public function update(UpdateTransactionRequest $request, string $transaction): JsonResponse
    {
        $data = $request->validated();
        $model = $this->transactions->update($request->user(), $transaction, $data);
        $model->load(['financialAccount', 'category']);
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return (new TransactionResource($model))->response();
    }

    public function destroy(Request $request, string $transaction): JsonResponse
    {
        $this->transactions->delete($request->user(), $transaction);
        $this->recommendationCache->bumpUserVersion($request->user()->id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
