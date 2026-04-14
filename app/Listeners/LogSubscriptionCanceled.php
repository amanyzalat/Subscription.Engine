<?php

namespace App\Listeners;

use App\Events\SubscriptionCanceled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogSubscriptionCanceled
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
    public function handle(SubscriptionCanceled $event): void
    {
        Log::warning('Subscription auto-canceled after grace period', [
            'subscription_id' => $event->subscription->id,
            'immediately'     => $event->immediately,
        ]);
    }
}
