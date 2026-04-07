<?php

namespace App\Http\Repositories\SubscriptionPayment;

use App\Http\Repositories\Base\BaseRepository;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\PlanPrice;
use App\Helpers\SubscriptionHelper;

class SubscriptionPaymentRepository extends BaseRepository
{
    public const GRACE_PERIOD_DAYS = 3;
    public function __construct(SubscriptionPayment $model)
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
     * Check if a transaction_id was already successfully recorded.
     */
    public function referenceExists(string $reference): bool
    {
        return SubscriptionPayment::where('transaction_id', $reference)
            ->where('status', 'succeeded')
            ->exists();
    }

    /**
     * Record a successful payment row.
     */
    public function createSucceeded(
        Subscription $subscription,
        ?string $paymentReference,
        Carbon $now
    ): SubscriptionPayment {
        return $this->model->create([
            'subscription_id'   => $subscription->id,
            'user_id'           => $subscription->user_id,
            'status'            => 'succeeded',
            'amount_cents'      => $subscription->price_cents,
            'currency'          => $subscription->currency,
            'payment_reference' => $paymentReference,
            'paid_at'           => $now,
        ]);
    }

    /**
     * Record a failed payment row.
     */
    public function createFailed(
        Subscription $subscription,
        ?string $failureReason
    ): SubscriptionPayment {
        return $this->model->create([
            'subscription_id' => $subscription->id,
            'user_id'         => $subscription->user_id,
            'status'          => 'failed',
            'amount_cents'    => $subscription->price_cents,
            'currency'        => $subscription->currency,
            'failure_reason'  => $failureReason,
        ]);
    }
}
