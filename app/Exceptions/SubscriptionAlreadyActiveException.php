<?php

namespace App\Exceptions;

use Exception;

class SubscriptionAlreadyActiveException extends Exception
{
    protected $message = 'Subscription is already active, no payment needed.';
}
