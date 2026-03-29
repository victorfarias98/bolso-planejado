<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFinancialAccountRequest;
use App\Http\Requests\Api\V1\UpdateFinancialAccountRequest;
use App\Http\Resources\Api\V1\FinancialAccountResource;
use App\Services\FinancialAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FinancialAccountController extends Controller
{
    public function __construct(
        private FinancialAccountService $accounts,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $list = $this->accounts->listForUser($request->user());

        return FinancialAccountResource::collection($list)->response();
    }

    public function store(StoreFinancialAccountRequest $request): JsonResponse
    {
        $account = $this->accounts->create($request->user(), $request->validated());

        return (new FinancialAccountResource($account))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, string $financialAccount): JsonResponse
    {
        $account = $this->accounts->find($request->user(), $financialAccount);

        return (new FinancialAccountResource($account))->response();
    }

    public function update(UpdateFinancialAccountRequest $request, string $financialAccount): JsonResponse
    {
        $account = $this->accounts->update($request->user(), $financialAccount, $request->validated());

        return (new FinancialAccountResource($account))->response();
    }

    public function destroy(Request $request, string $financialAccount): JsonResponse
    {
        $this->accounts->delete($request->user(), $financialAccount);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
