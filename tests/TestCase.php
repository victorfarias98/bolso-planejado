<?php

namespace Tests;

use Database\Seeders\BillingPlanSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BillingPlanSeeder::class);
    }
}
