<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProjectionResource;
use App\Services\ProjectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectionController extends Controller
{
    public function __construct(
        private ProjectionService $projection,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'horizon_days' => ['sometimes', 'integer', 'min:1', 'max:20000'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'financial_account_id' => [
                'nullable',
                'uuid',
                Rule::exists('financial_accounts', 'id')->where('user_id', $request->user()->id),
            ],
        ]);

        $horizon = (int) ($validated['horizon_days'] ?? 30);
        $accountId = $validated['financial_account_id'] ?? null;
        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        $payload = $this->projection->build($request->user(), $accountId, $horizon, $from, $to);

        return (new ProjectionResource($payload))->response();
    }
}
