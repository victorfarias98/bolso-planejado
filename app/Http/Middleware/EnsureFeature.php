<?php

namespace App\Http\Middleware;

use App\Services\EntitlementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeature
{
    public function __construct(
        private EntitlementService $entitlements
    ) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Não autenticado.'], Response::HTTP_UNAUTHORIZED);
        }

        if (! $this->entitlements->can($user, $feature)) {
            return response()->json([
                'message' => 'Este recurso está disponível em um plano superior.',
                'code' => 'feature_locked',
                'feature' => $feature,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
