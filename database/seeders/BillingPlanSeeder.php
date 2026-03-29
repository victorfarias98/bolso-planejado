<?php

namespace Database\Seeders;

use App\Enums\PlanBillingMode;
use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Seeder;
class BillingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $free = Plan::query()->updateOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Gratuito',
                'billing_mode' => PlanBillingMode::Free,
                'price_cents' => 0,
                'currency' => 'BRL',
                'interval' => null,
                'active' => true,
            ]
        );

        $premiumMonthly = Plan::query()->updateOrCreate(
            ['slug' => 'premium-monthly'],
            [
                'name' => 'Premium (mensal)',
                'billing_mode' => PlanBillingMode::Subscription,
                'price_cents' => 1990,
                'currency' => 'BRL',
                'interval' => 'month',
                'active' => true,
            ]
        );

        $premiumLifetime = Plan::query()->updateOrCreate(
            ['slug' => 'premium-lifetime'],
            [
                'name' => 'Premium (vitalício)',
                'billing_mode' => PlanBillingMode::OneTime,
                'price_cents' => 4900,
                'currency' => 'BRL',
                'interval' => null,
                'active' => true,
            ]
        );

        $featureDefs = [
            ['key' => 'recommendations', 'label' => 'Recomendações inteligentes'],
            ['key' => 'investments', 'label' => 'Investimentos'],
            ['key' => 'export_pdf', 'label' => 'Relatório PDF'],
            ['key' => 'max_accounts', 'label' => 'Limite de contas'],
        ];

        $features = [];
        foreach ($featureDefs as $def) {
            $features[$def['key']] = Feature::query()->updateOrCreate(
                ['key' => $def['key']],
                ['label' => $def['label']]
            );
        }

        $matrix = [
            $free->id => [
                'recommendations' => ['enabled' => false, 'limit_int' => null],
                'investments' => ['enabled' => false, 'limit_int' => null],
                'export_pdf' => ['enabled' => false, 'limit_int' => null],
                'max_accounts' => ['enabled' => true, 'limit_int' => 2],
            ],
            $premiumMonthly->id => [
                'recommendations' => ['enabled' => true, 'limit_int' => null],
                'investments' => ['enabled' => true, 'limit_int' => null],
                'export_pdf' => ['enabled' => true, 'limit_int' => null],
                'max_accounts' => ['enabled' => true, 'limit_int' => null],
            ],
            $premiumLifetime->id => [
                'recommendations' => ['enabled' => true, 'limit_int' => null],
                'investments' => ['enabled' => true, 'limit_int' => null],
                'export_pdf' => ['enabled' => true, 'limit_int' => null],
                'max_accounts' => ['enabled' => true, 'limit_int' => null],
            ],
        ];

        foreach ($matrix as $planId => $rows) {
            $plan = Plan::query()->findOrFail($planId);
            $sync = [];
            foreach ($rows as $key => $meta) {
                $sync[$features[$key]->id] = [
                    'enabled' => $meta['enabled'],
                    'limit_int' => $meta['limit_int'],
                ];
            }
            $plan->features()->sync($sync);
        }
    }
}
