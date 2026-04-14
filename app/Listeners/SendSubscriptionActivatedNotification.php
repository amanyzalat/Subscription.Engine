<?php

namespace App\Listeners;

use App\Events\SubscriptionActivated;

use App\Notifications\SubscriptionActivatedNotification;

class SendSubscriptionActivatedNotification
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
        $user = $event->subscription->user;

        $user->notify(
            new SubscriptionActivatedNotification($event->subscription)
        );
    }
}
