<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlanPrice\StorePlanPriceRequest;
use App\Http\Requests\PlanPrice\UpdatePlanPriceRequest;
use App\Http\Resources\PlanPrice\PlanPriceResource;
use Illuminate\Http\Request;
use App\Http\Repositories\PlanPrice\PlanPriceRepository;
use App\Services\ResponseService;

class PlanPriceController extends Controller
{
    public function __construct(private ResponseService $responseService, protected PlanPriceRepository $planPriceRepo)
    {
        $this->planPriceRepo = $planPriceRepo;
        $this->responseService = $responseService;
    }
    /**
     * GET /api/plan Prices
     */
    public function index(Request $request)
    {
        $planPriceRepo = $this->planPriceRepo->models($request);
        if (!$planPriceRepo['status']) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to get plans']]);
        }
        $planPriceRepo = PlanPriceResource::collection($planPriceRepo['data']);
        return $this->responseService->json('Success!',  $planPriceRepo, 200);
    }

    /**
     * POST /api/plan Price
     */
    public function store(StorePlanPriceRequest $request)
    {
        $planPriceRepo = $this->planPriceRepo->create($request->validated());
        if (!$planPriceRepo) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to create billing cycle']]);
        }
        $planPriceRepo = new PlanPriceResource($planPriceRepo);
        return $this->responseService->json('Success!',  $planPriceRepo, 201);
    }

    /**
     * GET /api/plans/{plan}
     */
    public function show($plan_id)
    {
        $planPriceRepo = $this->planPriceRepo->find($plan_id)->load('currency', 'plan', 'billingCycle');
        if (!$planPriceRepo) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to get plan']]);
        }
        $planPriceRepo = new PlanPriceResource($planPriceRepo);
        return $this->responseService->json('Success!',  $planPriceRepo, 200);
    }

    /**
     * PUT /api/plans/{plan}
     */
    public function update(UpdatePlanPriceRequest $request, $plan_id)
    {

        $planPriceRepo = $this->planPriceRepo->update($request->validated(), $plan_id);
        if (!$planPriceRepo) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to update billing cycle']]);
        }
        $planPriceRepo = new PlanPriceResource($planPriceRepo);
        return $this->responseService->json('Success!',  $planPriceRepo, 201);
    }

    /**
     * DELETE /api/plans/{plan}
     */
    public function destroy($plan_id)
    {
        $planPriceRepo = $this->planPriceRepo->delete($plan_id);
        if (!$planPriceRepo['status']) {
            return $this->responseService->json('Failed!', [], 401, ['error' => [$planPriceRepo['message']]]);
        }
        return $this->responseService->json('Success!', [], 200);
    }
}
