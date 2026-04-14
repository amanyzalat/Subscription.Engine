<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Resources\Subscription\SubscriptionResource;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Repositories\Subscription\SubscriptionRepository;
use App\Services\ResponseService;
use App\Repositories\Plan\PlanRepository;
use App\Repositories\PlanPrice\PlanPriceRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SubscriptionController extends Controller
{
    use AuthorizesRequests;
    public function __construct(

        private readonly SubscriptionService $subscriptionService,
        protected PlanRepository $planRepo,
        protected PlanPriceRepository $planPriceRepo,
        private ResponseService $responseService,
        protected SubscriptionRepository $subscriptionRepo
    ) {}

    /**
     * GET /api/subscriptions
     */
    public function index(Request $request)
    {
        $subscriptions = $this->subscriptionRepo->models($request);
        if (!$subscriptions['status']) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to get subscriptions']]);
        }
        $subscriptions = SubscriptionResource::collection($subscriptions['data']);
        return $this->responseService->json('Success!', $subscriptions, 200);
    }

    /**
     * POST /api/subscriptions
     * Subscribe the authenticated user to a plan.
     */
    public function store(StoreSubscriptionRequest $request)
    {
        $price = $this->planPriceRepo->findOrFail($request->validated('price_id'));

        if (! $price->plan->is_active) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['This plan is not available.']]);
        }

        $subscription = $this->subscriptionService->subscribe($request->user(), $price);
        $subscriptions = new SubscriptionResource($subscription->load(
            'price.plan',
            'price.currency',
            'price.billingCycle',
            'payments'
        ));
        return $this->responseService->json('Success!', $subscriptions, 201);
    }

    /**
     * GET /api/subscriptions/{subscription}
     */
    public function show(Request $request, Subscription $subscription)
    {

        $this->authorize('view', $subscription);

        $subscriptions = new SubscriptionResource($subscription->load(
            'price.plan',
            'price.currency',
            'price.billingCycle',
            'payments'
        ));
        return $this->responseService->json('Success!', $subscriptions, 200);
    }

    /**
     * DELETE /api/subscriptions/{subscription}
     * Cancel the subscription.
     */
    public function destroy(Request $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('cancel', $subscription);

        if ($subscription->isCanceled()) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Subscription is already canceled.']]);
        }

        $immediately = $request->boolean('immediately', false);
        $subscription = $this->subscriptionService->cancel($subscription, $immediately);

        return $this->responseService->json('Success!', new SubscriptionResource($subscription->load('price.plan', 'price.currency', 'price.billingCycle', 'payments')), 200);
    }

    /**
     * GET /api/subscriptions/{subscription}/access
     * Quick check: does the user currently have access?
     */
    public function checkAccess(Request $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('view', $subscription);
        return $this->responseService->json('Success!', [
            'has_access' => $subscription->hasAccess(),
            'status'     => $subscription->status,
        ], 200);
    }
}
