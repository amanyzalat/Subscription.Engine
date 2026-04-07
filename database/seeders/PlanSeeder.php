<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Currency;
use App\Models\BillingCycle;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $currencies = Currency::pluck('id', 'code')->toArray();          // ['AED' => 1, 'USD' => 2, ...]
        $billingCycles = BillingCycle::pluck('id', 'slug')->toArray();   // ['monthly' => 1, 'yearly' => 2]

        $plans = [
            [
                'plan' => [
                    'name'        => 'Starter',
                    'slug'        => 'starter',
                    'description' => 'Perfect for individuals getting started.',
                    'trial_days'  => 7,
                    'is_active'   => true,
                ],
                'prices' => [
                    ['billing_cycle' => 'monthly', 'currency' => 'AED', 'price_cents' => 9900],
                    ['billing_cycle' => 'yearly',  'currency' => 'AED', 'price_cents' => 99900],
                ],
            ],
            [
                'plan' => [
                    'name'        => 'Pro',
                    'slug'        => 'pro',
                    'description' => 'For growing teams.',
                    'trial_days'  => 7,
                    'is_active'   => true,
                ],
                'prices' => [
                    ['billing_cycle' => 'monthly', 'currency' => 'USD', 'price_cents' => 2999],
                    ['billing_cycle' => 'yearly',  'currency' => 'USD', 'price_cents' => 28799],
                ],
            ],
            [
                'plan' => [
                    'name'        => 'Business',
                    'slug'        => 'business',
                    'description' => 'Full-featured business plan, no trial.',
                    'trial_days'  => 0,
                    'is_active'   => true,
                ],
                'prices' => [
                    ['billing_cycle' => 'monthly', 'currency' => 'EGP', 'price_cents' => 149900],
                    ['billing_cycle' => 'yearly',  'currency' => 'EGP', 'price_cents' => 1499900],
                ],
            ],
        ];

        foreach ($plans as $data) {
            $plan = Plan::updateOrCreate(
                ['slug' => $data['plan']['slug']],
                $data['plan']
            );

            foreach ($data['prices'] as $priceData) {
                $currencyId = $currencies[$priceData['currency']] ?? null;
                $billingCycleId = $billingCycles[$priceData['billing_cycle']] ?? null;

                if (!$currencyId || !$billingCycleId) {
                    continue;
                }

                PlanPrice::updateOrCreate(
                    [
                        'plan_id'        => $plan->id,
                        'currency_id'    => $currencyId,
                        'billing_cycle_id' => $billingCycleId,
                    ],
                    [
                        'price_cents' => $priceData['price_cents'],
                        'price'       => $priceData['price_cents'] / 100,
                    ]
                );
            }
        }
    }
}
