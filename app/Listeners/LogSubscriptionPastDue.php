<?php

namespace App\Listeners;

use App\Events\SubscriptionPastDue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogSubscriptionPastDue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubscriptionPastDue $event): void
    {
        Log::info('Trial expired', [
            'subscription_id' => $event->subscription->id,
            'new_status'      => $event->subscription->fresh()->status,
        ]);
    }
}
