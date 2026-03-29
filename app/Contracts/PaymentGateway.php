<?php

namespace App\Contracts;

use App\Models\Plan;
use App\Models\User;

interface PaymentGateway
{
    /**
     * @return array{checkout_url: string|null, mode: string, plan_slug: string}
     */
    public function createCheckout(User $user, Plan $plan): array;

    public function verifyWebhookSignature(string $payload, string $signature): bool;
}
