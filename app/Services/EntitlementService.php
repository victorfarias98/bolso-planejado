<?php

namespace App\Services;

use App\Enums\PlanBillingMode;
use App\Enums\SubscriptionStatus;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\User;

class EntitlementService
{
    public function effectivePlan(User $user): Plan
    {
        // Billing desativado: evita tocar na base de planos
        if (filter_var(env('BILLING_DISABLED', true), FILTER_VALIDATE_BOOLEAN)) {
            $p = new Plan();
            $p->slug = 'free';
            $p->name = 'Gratuito';
            $p->billing_mode = PlanBillingMode::Free;
            $p->price_cents = 0;
            $p->currency = 'BRL';
            $p->interval = null;
            $p->active = true;

            return $p;
        }

        $subscription = $user->subscriptions()
            ->where('status', SubscriptionStatus::Active)
            ->where(function ($q): void {
                $q->whereNull('current_period_end')
                    ->orWhere('current_period_end', '>=', now()->startOfDay());
            })
            ->with('plan')
            ->latest('current_period_end')
            ->first();

        if ($subscription !== null && $subscription->plan !== null) {
            return $subscription->plan;
        }

        $purchase = $user->purchases()
            ->valid()
            ->with('plan')
            ->latest('paid_at')
            ->first();

        if ($purchase !== null && $purchase->plan !== null) {
            return $purchase->plan;
        }

        if ($user->plan_id !== null) {
            $plan = Plan::query()->find($user->plan_id);
            if ($plan !== null) {
                return $plan;
            }
        }

        return Plan::query()->where('slug', 'free')->firstOrFail();
    }

    /**
     * @return array<string, array{enabled: bool, limit: int|null}>
     */
    public function entitlements(User $user): array
    {
        // Billing desativado: sem recursos premium
        if (filter_var(env('BILLING_DISABLED', true), FILTER_VALIDATE_BOOLEAN)) {
            return [];
        }

        $plan = $this->effectivePlan($user);
        $features = Feature::query()->orderBy('key')->get();
        $pivot = $plan->features()->get()->keyBy('id');

        $out = [];
        foreach ($features as $feature) {
            $row = $pivot->get($feature->id);
            $out[$feature->key] = [
                'enabled' => $row ? (bool) $row->pivot->enabled : false,
                'limit' => $row?->pivot->limit_int,
            ];
        }

        return $out;
    }

    public function can(User $user, string $featureKey): bool
    {
        $all = $this->entitlements($user);
        if (! isset($all[$featureKey])) {
            return false;
        }

        return $all[$featureKey]['enabled'] === true;
    }

    public function limit(User $user, string $featureKey): ?int
    {
        $all = $this->entitlements($user);

        return $all[$featureKey]['limit'] ?? null;
    }

    public function syncUserPlanSnapshot(User $user): void
    {
        $plan = $this->effectivePlan($user);
        $user->forceFill(['plan_id' => $plan->id])->save();

        if ($plan->billing_mode !== PlanBillingMode::Free) {
            $sub = $user->subscriptions()
                ->where('status', SubscriptionStatus::Active)
                ->where(function ($q): void {
                    $q->whereNull('current_period_end')
                        ->orWhere('current_period_end', '>=', now()->startOfDay());
                })
                ->latest('current_period_end')
                ->first();

            if ($sub !== null && $sub->current_period_end !== null) {
                $user->forceFill(['premium_expires_at' => $sub->current_period_end])->save();

                return;
            }

            $purchase = $user->purchases()->valid()->latest('paid_at')->first();
            if ($purchase !== null) {
                $user->forceFill(['premium_expires_at' => $purchase->expires_at])->save();

                return;
            }
        }

        $user->forceFill(['premium_expires_at' => null])->save();
    }
}
