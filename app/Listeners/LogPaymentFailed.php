<?php

namespace App\Listeners;

use App\Events\PaymentFailed;
use Illuminate\Support\Facades\Log;

class LogPaymentFailed
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
    public function handle(PaymentFailed $event): void
    {
        Log::warning('Payment failed – grace period opened', [
            'subscription_id'      => $event->subscription->id,
            'grace_period_ends_at' => $event->subscription->grace_period_ends_at,
        ]);
    }
}
