<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ResponseService;
use App\Http\Requests\Billing\StoreBillingRequest;
use App\Http\Resources\Billing\BillingResource;
use App\Repositories\Billing\BillingRepository;


class BillingCycleController extends Controller
{
    public function __construct(private ResponseService $responseService, protected BillingRepository $billingCycleRepo)
    {
        $this->billingCycleRepo = $billingCycleRepo;
        $this->responseService = $responseService;
    }
    /**
     * POST /api/billing-cycles
     */
    public function store(StoreBillingRequest $request)
    {

        $billingCycle = $this->billingCycleRepo->create($request->validated());
        if (!$billingCycle) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to create billing cycle']]);
        }
        $billingCycle = new BillingResource($billingCycle);
        return $this->responseService->json('Success!',  $billingCycle, 201);
    }
}
