<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\SubscriptionPayment;

class SubscriptionActivated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public ?SubscriptionPayment $payment = null
    ) {}
}
