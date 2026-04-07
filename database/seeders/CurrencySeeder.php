<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'USD', 'symbol' => '$'],
            ['code' => 'AED', 'symbol' => 'AED'],
            ['code' => 'EGP', 'symbol' => 'EGP'],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                ['symbol' => $currency['symbol']]
            );
        }
    }
}
