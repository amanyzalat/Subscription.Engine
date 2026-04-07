<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id'               => User::factory(),
            'plan_id'               => Plan::factory(),
            'price_id'              => PlanPrice::factory(),
            'status'                => SubscriptionStatus::ACTIVE->value,
            'current_period_start'  => now(),
            'current_period_end'    => now()->addMonth(),
        ];
    }

    public function trialing(int $daysLeft = 7): static
    {
        return $this->state([
            'status'               => SubscriptionStatus::TRIALING->value,
            'trial_ends_at'        => now()->addDays($daysLeft),
            'current_period_start' => null,
            'current_period_end'   => null,
        ]);
    }

    public function pastDue(int $graceDaysLeft = 2): static
    {
        return $this->state([
            'status'               => SubscriptionStatus::PAST_DUE->value,
            'grace_period_ends_at' => now()->addDays($graceDaysLeft),
        ]);
    }

    public function canceled(): static
    {
        return $this->state([
            'status'      => SubscriptionStatus::CANCELED->value,
            'canceled_at' => now(),
            'ends_at'     => now(),
        ]);
    }
}
