<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\RecommendationCacheService;
use App\Services\RecommendationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(
        private RecommendationService $recommendations,
        private RecommendationCacheService $recommendationCache,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        $periodStart = $from ? Carbon::parse($from)->startOfDay() : Carbon::today()->startOfMonth();
        $periodEnd = $to ? Carbon::parse($to)->endOfDay() : Carbon::today()->endOfMonth()->endOfDay();
        if ($periodEnd->lt($periodStart)) {
            $periodEnd = $periodStart->copy()->endOfDay();
        }

        // Cache mensal por usuário e faixa de datas, expirando em D+30 do período solicitado.
        $cacheKey = $this->recommendationCache->buildKey(
            $request->user()->id,
            $periodStart,
            $periodEnd,
        );
        $expiresAt = $this->recommendationCache->expiresAt($periodEnd);

        $payload = Cache::remember($cacheKey, $expiresAt, function () use ($request, $from, $to) {
            return $this->recommendations->build(
                $request->user(),
                $from,
                $to,
            );
        });

        return response()->json([
            'data' => $payload,
        ]);
    }
}
