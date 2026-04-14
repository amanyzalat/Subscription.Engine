<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\SubscriptionHelper;
use Carbon\Carbon;
use App\Models\User;
use App\Models\PlanPrice;
use App\Http\Repositories\Subscription\SubscriptionRepository;
use App\Exceptions\SubscriptionAlreadyActiveException;
use App\Exceptions\DuplicatePaymentReferenceException;
use App\Http\Repositories\SubscriptionPayment\SubscriptionPaymentRepository;
use App\Events\SubscriptionActivated;
use App\Events\PaymentFailed;
use App\Events\SubscriptionCanceled;
use App\Events\SubscriptionPastDue;
use App\Events\SubscriptionCreated;

class SubscriptionService
{

    public const GRACE_PERIOD_DAYS = 3;
    public function __construct(private readonly SubscriptionRepository $subscriptionRepo, private readonly  SubscriptionPaymentRepository      $paymentRepo) {}
     // -------------------------------------------------------------------------
    // Subscribing
    // -------------------------------------------------------------------------

    /**
     * Subscribe a user to a plan with a specific price.
     */
    public function subscribe(User $user,  PlanPrice $planPrice): Subscription
    {
        SubscriptionHelper::cancelExistingSubscriptions(
            $user,
            fn($s, $immediately) => $this->cancel($s, $immediately)
        );


        return DB::transaction(function () use ($user, $planPrice) {
            $now = Carbon::now();
            $plan = $planPrice->plan;

            $attributes = [
                'user_id'  => $user->id,
                'plan_id'  => $plan->id,
                'price_id' => $planPrice->id,
            ];

            if ($plan->hasTrialPeriod()) {
                $attributes['status']        = SubscriptionStatus::TRIALING->value;
                $attributes['trial_ends_at'] = $now->copy()->addDays($plan->trial_days);
            } else {
                $attributes['status']               = SubscriptionStatus::ACTIVE->value;
                $attributes['current_period_start'] = $now;
                $attributes['current_period_end']   = SubscriptionHelper::nextBillingDate($planPrice, $now);
            }
            $subscription = $this->subscriptionRepo->create($attributes);
            event(new SubscriptionCreated($subscription));
            return $subscription;
        });
    }
    // -------------------------------------------------------------------------
    // Cancellation
    // -------------------------------------------------------------------------

    public function cancel(Subscription $subscription, bool $immediately = false): Subscription
    {
        if ($subscription->isCanceled()) {
            return $subscription;
        }

        $now = Carbon::now();
        $endsAt = $immediately ? $now : $subscription->current_period_end;

        $subscription = $this->subscriptionRepo->cancel($subscription, $now, $endsAt);

        event(new SubscriptionCanceled($subscription, $immediately));


        return $subscription->refresh();
    }
    // -------------------------------------------------------------------------
    // Payment recording
    // -------------------------------------------------------------------------

    /**
     * Record a successful payment and activate the subscription.
     
     */
    public function recordSuccessfulPayment(Subscription $subscription, ?string $paymentReference = null)
    {
        return DB::transaction(function () use ($subscription, $paymentReference) {

            // Guard 1: duplicate payment_reference
            if ($paymentReference && $this->paymentRepo->referenceExists($paymentReference)) {
                throw new DuplicatePaymentReferenceException();
            }

            // Guard 2: subscription already active
            if ($subscription->isActive()) {
                throw new SubscriptionAlreadyActiveException();
            }

            $now      = Carbon::now();
            $payment  = $this->paymentRepo->createSucceeded($subscription, $paymentReference, $now);
            $periodEnd = SubscriptionHelper::nextBillingDate($subscription->price, $now);

            $updatedSubscription = $this->subscriptionRepo->activateAfterPayment($subscription, $now, $periodEnd);

            event(new SubscriptionActivated($updatedSubscription, $payment));
            return $payment;
        });
    }

    /**
     * Record a failed payment and open a 3-day grace period.
     
     */
    public function recordFailedPayment(Subscription $subscription, ?string $failureReason = null)
    {
        return DB::transaction(function () use ($subscription, $failureReason) {
            $payment = $this->paymentRepo->createFailed($subscription, $failureReason);

            if (! $subscription->isCanceled()) {
                $gracePeriodEnd = Carbon::now()->addDays(self::GRACE_PERIOD_DAYS);

                $subscription = $this->subscriptionRepo->markAsPastDue($subscription, $gracePeriodEnd);
                event(new PaymentFailed($subscription));
            }

            return $payment;
        });
    }
 

    // -------------------------------------------------------------------------
    // Automated lifecycle transitions (scheduler)
    // -------------------------------------------------------------------------


    /**
     * Expire trials whose trial_ends_at has passed.
     */
    public function expireTrials(): int
    {
        $count = 0;

        foreach ($this->subscriptionRepo->getExpiredTrials() as $subscription) {
            DB::transaction(function () use ($subscription) {
                $now = Carbon::now();

                if ($subscription->price_cents === 0) {
                    // Free plan → activate directly
                    $periodEnd = SubscriptionHelper::nextBillingDate($subscription->price, $now);
                    $subscription = $this->subscriptionRepo->expireTrialToActive($subscription, $now, $periodEnd);
                    event(new SubscriptionActivated($subscription->fresh()));
                } else {
                    // Paid plan → past_due, await first payment
                    $gracePeriodEnd = $now->copy()->addDays(self::GRACE_PERIOD_DAYS);
                    $subscription = $this->subscriptionRepo->expireTrialToPastDue($subscription, $gracePeriodEnd);
                    event(new SubscriptionPastDue($subscription->fresh(), $gracePeriodEnd));
                }
            });

            $count++;
        }

        return $count;
    }
    /**
     * Expire grace periods of past_due subscriptions whose grace_period_ends_at has passed.
     */
    public function cancelExpiredGracePeriods(): int
    {
        $count = 0;
        $now = Carbon::now();
        foreach ($this->subscriptionRepo->getExpiredGracePeriods() as $subscription) {

            $subscription = $this->subscriptionRepo->cancel($subscription, $now, $now);

            $count++;
            event(new SubscriptionCanceled($subscription));
        }

        return $count;
    }
}
