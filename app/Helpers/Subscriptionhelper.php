<?php

namespace App\Helpers;

use App\Enums\SubscriptionStatus;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionHelper
{
    /**
     * Calculate the next billing date based on the plan's billing cycle.
     *
     */
    public static function nextBillingDate(PlanPrice $price, Carbon $from): Carbon
    {
        $months = $price->billingCycle?->months ?? 1;

        return $from->copy()->addMonths($months);
    }

    /**
     * Cancel all active/trialing/past_due subscriptions for a user.
     * Called before creating a new subscription to enforce one-at-a-time rule.
     */
    public static function cancelExistingSubscriptions(User $user, callable $cancel): void
    {
        $user->subscriptions()
            ->whereIn('status', [
                SubscriptionStatus::TRIALING->value,
                SubscriptionStatus::ACTIVE->value,
                SubscriptionStatus::PAST_DUE->value,
            ])
            ->each(fn(Subscription $s) => $cancel($s, true));
    }
}
