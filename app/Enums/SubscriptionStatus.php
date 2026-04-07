<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case TRIALING = 'trialing';
    case ACTIVE   = 'active';
    case PAST_DUE = 'past_due';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::TRIALING => 'Trialing',
            self::ACTIVE   => 'Active',
            self::PAST_DUE => 'Past Due',
            self::CANCELED => 'Canceled',
        };
    }

    public function allowsAccess(): bool
    {
        return match ($this) {
            self::TRIALING, self::ACTIVE, self::PAST_DUE => true,
            self::CANCELED => false,
        };
    }
}
