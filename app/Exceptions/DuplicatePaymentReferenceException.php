<?php

namespace App\Exceptions;

use Exception;

class DuplicatePaymentReferenceException extends Exception
{
    protected $message = 'Payment reference already exists.';
}
