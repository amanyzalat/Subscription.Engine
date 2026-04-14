<?php

namespace App\Listeners;

use App\Events\SubscriptionActivated;
use Illuminate\Support\Facades\Log;

class LogSubscriptionActivated
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
    public function handle(SubscriptionActivated $event): void
    {
        Log::info('Payment succeeded – subscription activated', [
            'subscription_id' => $event->subscription->id,
            'user_id' => $event->subscription->user_id,
            'payment_id' => $event->payment->id
        ]);
    }
}
