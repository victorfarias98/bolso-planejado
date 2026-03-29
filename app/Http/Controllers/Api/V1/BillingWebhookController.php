<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BillingWebhookController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'message' => 'Webhook de pagamento ainda não configurado para o driver em uso.',
        ], Response::HTTP_NOT_IMPLEMENTED);
    }
}
