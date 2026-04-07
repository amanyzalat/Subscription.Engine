<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case SUCCEEDED   = 'succeeded';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SUCCEEDED   => 'Succeeded',
            self::FAILED => 'Failed',
        };
    }

    public function allowsAccess(): bool
    {
        return match ($this) {
            self::PENDING, self::SUCCEEDED => true,
            self::FAILED => false,
        };
    }
}
