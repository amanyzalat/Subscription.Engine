<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    public function cancel(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    /**
     * In a real application you'd gate this to admin / webhook user.
     * For demonstration it mirrors the owner check.
     */
    public function managePayments(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }
}
