<?php

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\EntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Garante plano premium (assinatura mensal fake) para testes de rotas com middleware de feature.
 */
function grantPremiumMonthly(User $user): void
{
    $plan = Plan::query()->where('slug', 'premium-monthly')->firstOrFail();
    Subscription::query()->updateOrCreate(
        [
            'user_id' => $user->id,
            'external_id' => 'test-sub-'.$user->id,
        ],
        [
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'current_period_start' => now()->subDay(),
            'current_period_end' => now()->addMonth(),
            'gateway' => 'fake',
        ]
    );
    app(EntitlementService::class)->syncUserPlanSnapshot($user);
}
