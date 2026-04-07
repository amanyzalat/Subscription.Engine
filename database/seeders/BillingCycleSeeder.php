<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BillingCycle;

class BillingCycleSeeder extends Seeder
{
    public function run(): void
    {
        $billingCycles = [
            ['name' => 'Monthly', 'slug' => 'monthly', 'months' => 1],
            ['name' => 'Yearly', 'slug' => 'yearly', 'months' => 12],
        ];

        foreach ($billingCycles as $cycle) {
            BillingCycle::updateOrCreate(
                ['slug' => $cycle['slug']],
                ['name' => $cycle['name'], 'months' => $cycle['months']]
            );
        }
    }
}
