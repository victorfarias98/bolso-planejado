<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\PaymentGateway;
use App\Enums\PlanBillingMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckoutRequest;
use App\Http\Resources\Api\V1\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BillingController extends Controller
{
    public function plans(): JsonResponse
    {
        $plans = Plan::query()
            ->where('active', true)
            ->whereNot('billing_mode', PlanBillingMode::Free)
            ->orderBy('price_cents')
            ->get();

        return PlanResource::collection($plans)->response();
    }

    public function checkout(CheckoutRequest $request, PaymentGateway $gateway): JsonResponse
    {
        $plan = Plan::query()
            ->where('slug', $request->validated('plan_slug'))
            ->where('active', true)
            ->firstOrFail();

        if ($plan->billing_mode === PlanBillingMode::Free) {
            return response()->json([
                'message' => 'Este plano não requer checkout.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $gateway->createCheckout($request->user(), $plan);

        return response()->json($payload, Response::HTTP_CREATED);
    }
}
