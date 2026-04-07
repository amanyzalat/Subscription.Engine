<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\BillingCycleSeeder;
use Database\Seeders\PlanSeeder;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            UserSeeder::class,
            CurrencySeeder::class,
            BillingCycleSeeder::class,
            PlanSeeder::class,
        ]);
    }
}
