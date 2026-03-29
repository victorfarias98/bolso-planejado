<?php

namespace App\Services\Billing;

use App\Contracts\PaymentGateway;
use App\Enums\PlanBillingMode;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Purchase;
use App\Models\Subscription;
use App\Models\User;
use App\Services\EntitlementService;
use Carbon\Carbon;

class FakePaymentGateway implements PaymentGateway
{
    public function __construct(
        private EntitlementService $entitlements
    ) {}

    public function createCheckout(User $user, Plan $plan): array
    {
        if ($plan->billing_mode === PlanBillingMode::Free) {
            return [
                'checkout_url' => null,
                'mode' => 'noop',
                'plan_slug' => $plan->slug,
            ];
        }

        if ($plan->billing_mode === PlanBillingMode::Subscription) {
            $start = now();
            $end = $plan->interval === 'year'
                ? $start->copy()->addYear()
                : $start->copy()->addMonth();

            Subscription::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'external_id' => 'fake-sub-'.$user->id.'-'.$plan->id,
                ],
                [
                    'plan_id' => $plan->id,
                    'status' => SubscriptionStatus::Active,
                    'current_period_start' => $start,
                    'current_period_end' => $end,
                    'gateway' => 'fake',
                ]
            );
        }

        if ($plan->billing_mode === PlanBillingMode::OneTime) {
            Subscription::query()
                ->where('user_id', $user->id)
                ->where('status', SubscriptionStatus::Active)
                ->update(['status' => SubscriptionStatus::Canceled]);

            Purchase::query()->create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'amount_cents' => $plan->price_cents,
                'paid_at' => Carbon::now(),
                'expires_at' => null,
                'gateway' => 'fake',
                'external_payment_id' => 'fake-pay-'.uniqid('', true),
            ]);
        }

        $this->entitlements->syncUserPlanSnapshot($user);
        $user->refresh();

        return [
            'checkout_url' => null,
            'mode' => 'simulated',
            'plan_slug' => $plan->slug,
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        return false;
    }
}
