<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\RecordPaymentRequest;
use App\Http\Resources\Payment\PaymentResource;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Http\Repositories\SubscriptionPayment\SubscriptionPaymentRepository;
use App\Services\ResponseService;

class SubscriptionPaymentController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        protected SubscriptionPaymentRepository $subscriptionPaymentRepo,
        private ResponseService $responseService,
    ) {
        $this->subscriptionPaymentRepo = $subscriptionPaymentRepo;
    }

    /**
     * GET /api/subscriptions/{subscription}/payments
     */
    public function index(Request $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('view', $subscription);

        $payments =  $this->subscriptionPaymentRepo->find($subscription->id);

        if (!$payments) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to get payments']]);
        }
        $payments = new PaymentResource($payments);
        return $this->responseService->json('Success!', $payments, 200);
    }

    /**
     * POST /api/subscriptions/{subscription}/payments/succeed
     * Simulate / record a successful payment.
     */
    public function succeed(RecordPaymentRequest $request, Subscription $subscription)
    {

        $this->authorize('managePayments', $subscription);

        if ($subscription->isCanceled()) {
            return $this->responseService->json('Failed!', [], 422, ['error' => ['Cannot record payment for a canceled subscription.']]);
        }

        $payment = $this->subscriptionService->recordSuccessfulPayment(
            $subscription,
            $request->validated('transaction_id')
        );

        return $this->responseService->json('Success!', new PaymentResource($payment), 201);
    }

    /**
     * POST /api/subscriptions/{subscription}/payments/fail
     * Simulate / record a failed payment.
     */
    public function fail(RecordPaymentRequest $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('managePayments', $subscription);

        if ($subscription->isCanceled()) {
            return $this->responseService->json('Failed!', [], 422, ['error' => ['Cannot record payment for a canceled subscription.']]);
        }

        $payment = $this->subscriptionService->recordFailedPayment(
            $subscription,
            $request->validated('failure_reason')
        );

        return $this->responseService->json('Success!', new PaymentResource($payment), 201);
    }
}
