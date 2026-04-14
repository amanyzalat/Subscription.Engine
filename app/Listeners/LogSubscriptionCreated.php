<?php

namespace App\Listeners;

use App\Events\SubscriptionCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogSubscriptionCreated
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
    public function handle(SubscriptionCreated $event): void
    {
        Log::info('Subscription created', [
            'user_id'         => $event->subscription->user_id,
            'plan_id'         => $event->subscription->plan_id,
            'price_id'        => $event->subscription->price_id,
            'status'          => $event->subscription->status,
            'subscription_id' => $event->subscription->id,
        ]);
    }
}
