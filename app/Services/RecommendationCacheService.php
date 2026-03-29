<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

class RecommendationCacheService
{
    public function buildKey(string $userId, CarbonInterface $periodStart, CarbonInterface $periodEnd): string
    {
        return sprintf(
            'recommendations:%s:v%s:%s:%s:%s',
            $userId,
            $this->versionForUser($userId),
            $periodStart->format('Y-m'),
            $periodStart->toDateString(),
            $periodEnd->toDateString(),
        );
    }

    public function expiresAt(CarbonInterface $periodEnd): CarbonInterface
    {
        return $periodEnd->copy()->addDays(30);
    }

    public function bumpUserVersion(string $userId): void
    {
        $versionKey = $this->versionKey($userId);
        if (!Cache::has($versionKey)) {
            Cache::forever($versionKey, 1);
        }
        Cache::increment($versionKey);
    }

    private function versionForUser(string $userId): int
    {
        $versionKey = $this->versionKey($userId);
        $version = Cache::get($versionKey);
        if ($version === null) {
            Cache::forever($versionKey, 1);
            return 1;
        }

        return max(1, (int) $version);
    }

    private function versionKey(string $userId): string
    {
        return sprintf('recommendations:version:%s', $userId);
    }
}
