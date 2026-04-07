<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name'        => ucwords($name),
            'slug'        => Str::slug($name . '-' . $this->faker->unique()->numberBetween(1, 9999)),
            'description' => $this->faker->sentence(),
            'trial_days'  => $this->faker->randomElement([0, 7, 14]),
            'is_active'   => true,
        ];
    }

    public function withTrial(int $days = 7): static
    {
        return $this->state(['trial_days' => $days]);
    }

    public function noTrial(): static
    {
        return $this->state(['trial_days' => 0]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
