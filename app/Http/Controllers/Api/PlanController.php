<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\StorePlanRequest;
use App\Http\Requests\Plan\UpdatePlanRequest;
use App\Http\Resources\Plan\PlanResource;
use Illuminate\Http\Request;
use App\Repositories\Plan\PlanRepository;
use App\Http\Resources\Plan\PlanPricesResource;
use App\Services\ResponseService;

class PlanController extends Controller
{
    public function __construct(private ResponseService $responseService, protected PlanRepository $planRepo)
    {
        $this->planRepo = $planRepo;
        $this->responseService = $responseService;
    }
    /**
     * GET /api/plans
     */
    public function index(Request $request)
    {
        $planRepo = $this->planRepo->models($request);
        if (!$planRepo['status']) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to get plans']]);
        }
        $planRepo = PlanResource::collection($planRepo['data']);
        return $this->responseService->json('Success!',  $planRepo, 200);
    }
    /**
     * GET /api/active-plans-with-prices    
     */
    public function activePlansWithPrices($plan_id)
    {
        $planRepo = $this->planRepo->activePlansWithPrices($plan_id);
        if (!$planRepo['status']) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to get plans']]);
        }
        $planRepo = new PlanPricesResource($planRepo['data']);
        return $this->responseService->json('Success!',  $planRepo, 200);
    }

    /**
     * POST /api/plans
     */
    public function store(StorePlanRequest $request)
    {
        $planRepo = $this->planRepo->create($request->validated());
        if (!$planRepo) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to create billing cycle']]);
        }
        $planRepo = new PlanResource($planRepo);
        return $this->responseService->json('Success!',  $planRepo, 201);
    }

    /**
     * GET /api/plans/{plan}
     */
    public function show($plan_id)
    {
        $planRepo = $this->planRepo->find($plan_id);
        if (!$planRepo) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to get plan']]);
        }
        $planRepo = new PlanResource($planRepo);
        return $this->responseService->json('Success!',  $planRepo, 200);
    }

    /**
     * PUT /api/plans/{plan}
     */
    public function update(UpdatePlanRequest $request, $plan_id)
    {

        $planRepo = $this->planRepo->update($request->validated(), $plan_id);
        if (!$planRepo) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to update billing cycle']]);
        }
        $planRepo = new PlanResource($planRepo);
        return $this->responseService->json('Success!',  $planRepo, 201);
    }

    /**
     * DELETE /api/plans/{plan}
     */
    public function destroy($plan_id)
    {
        $planRepo = $this->planRepo->delete($plan_id);
        if (!$planRepo['status']) {
            return $this->responseService->json('Failed!', [], 401, ['error' => [$planRepo['message']]]);
        }
        return $this->responseService->json('Success!', [], 201);
    }
}
