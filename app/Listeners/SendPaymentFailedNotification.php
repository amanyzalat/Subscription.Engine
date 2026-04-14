<?php

namespace App\Listeners;

use App\Events\PaymentFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\PaymentFailedNotification;

class SendPaymentFailedNotification
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
        $event->subscription->user->notify(
            new PaymentFailedNotification(
                $event->subscription
            )
        );
    }
}
