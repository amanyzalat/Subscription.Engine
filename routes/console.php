<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Run the lifecycle command manually any time:
|   php artisan subscriptions:process-lifecycle
*/

Artisan::command('subscriptions:status', function () {
    $this->table(
        ['Status', 'Count'],
        [
            ['Trialing', \App\Models\Subscription::trialing()->count()],
            ['Active',   \App\Models\Subscription::active()->count()],
            ['Past Due', \App\Models\Subscription::pastDue()->count()],
            ['Canceled', \App\Models\Subscription::canceled()->count()],
        ]
    );
})->purpose('Show a summary of current subscription statuses.');
