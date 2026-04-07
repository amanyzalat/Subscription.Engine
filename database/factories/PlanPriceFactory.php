<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Currency;
use App\Models\BillingCycle;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanPriceFactory extends Factory
{
    protected $model = PlanPrice::class;

    public function definition(): array
    {
        $priceCents = $this->faker->randomElement([999, 1999, 4999, 9900, 29900]);

        return [
            'plan_id' => Plan::factory(),

            'currency_id' => Currency::inRandomOrder()->value('id')
                ?? Currency::factory(),

            'billing_cycle_id' => BillingCycle::inRandomOrder()->value('id')
                ?? BillingCycle::factory(),

            'price_cents' => $priceCents,
            'price'       => $priceCents / 100,
        ];
    }

    public function monthly(): static
    {
        return $this->state(function () {
            return [
                'billing_cycle_id' => BillingCycle::where('slug', 'monthly')->value('id'),
            ];
        });
    }

    public function yearly(): static
    {
        return $this->state(function () {
            return [
                'billing_cycle_id' => BillingCycle::where('slug', 'yearly')->value('id'),
            ];
        });
    }

    public function free(): static
    {
        return $this->state([
            'price_cents' => 0,
            'price'       => 0.00,
        ]);
    }
}
