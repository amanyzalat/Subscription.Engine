<?php

namespace App\Http\Repositories\Subscription;

use App\Http\Repositories\Base\BaseRepository;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Enums\SubscriptionStatus;
use App\Helpers\SubscriptionHelper;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionRepository extends BaseRepository
{
    public function __construct(Subscription $model)
    {
        parent::__construct($model);
    }
    public function models($request)
    {
        [$sort, $order] = $this->setSortParams($request, 'created_at', 'desc');
        $models = $this->model->where(function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        });
        $models->with(['plan', 'payments']);
        $models->orderBy($sort, $order);
        // default per_page = 10
        $perPage = $request->input('per_page', 10);

        $models = $models->paginate($perPage);
        return ['status' => true, 'data' => $models];
    }
    /**
     * Update a subscription's attributes.
     */
    public function updates(Subscription $subscription, array $attributes): Subscription
    {
        $subscription->update($attributes);
        return $subscription->refresh();
    }
    /**
     * Transition a subscription to canceled.
     */
    public function cancel(Subscription $subscription, Carbon $now, ?Carbon $endsAt): Subscription
    {
        return $this->updates($subscription, [
            'status'      => SubscriptionStatus::CANCELED->value,
            'canceled_at' => $now,
            'ends_at'     => $endsAt ?? $now,
        ]);
    }
    /**
     * Transition an expired trial subscription to past_due with a grace period.
     */
    public function expireTrialToPastDue(Subscription $subscription, Carbon $gracePeriodEnd): Subscription
    {
        return $this->updates($subscription, [
            'status'               => SubscriptionStatus::PAST_DUE->value,
            'grace_period_ends_at' => $gracePeriodEnd,
        ]);
    }
    /**
     * Get all trialing subscriptions whose trial has expired.
     */
    public function getExpiredTrials()
    {
        return Subscription::expiredTrials()->with('plan')->get();
    }
    /**
     * Transition an expired trial of a free plan straight to active.
     */
    public function expireTrialToActive(Subscription $subscription, Carbon $now, Carbon $periodEnd): Subscription
    {
        return $this->updates($subscription, [
            'status'               => SubscriptionStatus::ACTIVE->value,
            'current_period_start' => $now,
            'current_period_end'   => $periodEnd,
        ]);
    }
    /**
     * Get all past_due subscriptions whose grace period has expired.
     */
    public function getExpiredGracePeriods()
    {
        return Subscription::expiredGracePeriods()->get();
    }
    /**
     * Transition a subscription to active status after a successful payment.
     */
    public function activateAfterPayment(Subscription $subscription, Carbon $now, Carbon $periodEnd): Subscription
    {
        return $this->updates($subscription, [
            'status'               => SubscriptionStatus::ACTIVE->value,
            'grace_period_ends_at' => null,
            'current_period_start' => $now,
            'current_period_end'   => $periodEnd,
        ]);
    }
    /**
     * Transition a subscription to past_due and open a grace period.
     */
    public function markAsPastDue(Subscription $subscription, Carbon $gracePeriodEnd): Subscription
    {
        return $this->updates($subscription, [
            'status'               => SubscriptionStatus::PAST_DUE->value,
            'grace_period_ends_at' => $gracePeriodEnd,
        ]);
    }
}
