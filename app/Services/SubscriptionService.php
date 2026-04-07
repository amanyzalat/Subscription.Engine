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
use App\Models\SubscriptionPayment;
use App\Http\Repositories\SubscriptionPayment\SubscriptionPaymentRepository;

class SubscriptionService
{

    public const GRACE_PERIOD_DAYS = 3;
    public function __construct(private readonly SubscriptionRepository $subscriptionRepo, private readonly  SubscriptionPaymentRepository      $paymentRepo,) {}
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

            Log::info('Subscription created', [
                'user_id'         => $user->id,
                'plan_id'         => $plan->id,
                'price_id'        => $planPrice->id,
                'status'          => $subscription->status,
                'subscription_id' => $subscription->id,
            ]);

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


        Log::info('Subscription canceled', [
            'subscription_id' => $subscription->id,
            'immediately'     => $immediately,
        ]);

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
                throw new \RuntimeException(
                    "Payment reference [{$paymentReference}] already recorded."
                );
            }

            // Guard 2: subscription already active
            if ($subscription->isActive()) {
                throw new \RuntimeException(
                    'Subscription is already active, no payment needed.'
                );
            }

            $now      = Carbon::now();
            $payment  = $this->paymentRepo->createSucceeded($subscription, $paymentReference, $now);
            $periodEnd = SubscriptionHelper::nextBillingDate($subscription->price, $now);

            $this->subscriptionRepo->activateAfterPayment($subscription, $now, $periodEnd);

            Log::info('Payment succeeded – subscription activated', [
                'subscription_id' => $subscription->id,
                'payment_id'      => $payment->id,
            ]);

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

                $this->subscriptionRepo->markAsPastDue($subscription, $gracePeriodEnd);

                Log::warning('Payment failed – grace period opened', [
                    'subscription_id'      => $subscription->id,
                    'grace_period_ends_at' => $gracePeriodEnd,
                ]);
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
                    $this->subscriptionRepo->expireTrialToActive($subscription, $now, $periodEnd);
                } else {
                    // Paid plan → past_due, await first payment
                    $gracePeriodEnd = $now->copy()->addDays(self::GRACE_PERIOD_DAYS);
                    $this->subscriptionRepo->expireTrialToPastDue($subscription, $gracePeriodEnd);
                }
            });

            $count++;

            Log::info('Trial expired', [
                'subscription_id' => $subscription->id,
                'new_status'      => $subscription->fresh()->status,
            ]);
        }

        return $count;
    }
    /**
     * Expire grace periods of past_due subscriptions whose grace_period_ends_at has passed.
     */
    public function cancelExpiredGracePeriods(): int
    {
        $count = 0;

        foreach ($this->subscriptionRepo->getExpiredGracePeriods() as $subscription) {
            $now = Carbon::now();
            $this->subscriptionRepo->cancel($subscription, $now, $now);

            $count++;

            Log::warning('Subscription auto-canceled after grace period', [
                'subscription_id' => $subscription->id,
            ]);
        }

        return $count;
    }
}
