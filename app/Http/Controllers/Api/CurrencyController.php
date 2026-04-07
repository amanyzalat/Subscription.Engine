<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Currency\CurrencyRepository;
use App\Http\Requests\Currency\StoreCurrencyRequest;
use App\Services\ResponseService;
use App\Http\Resources\Currency\CurrencyResource;

class CurrencyController extends Controller
{


    public function __construct(private ResponseService $responseService, protected CurrencyRepository $currencyRepo)
    {
        $this->currencyRepo = $currencyRepo;
        $this->responseService = $responseService;
    }
    /**
     * POST /api/currencies
     */
    public function store(StoreCurrencyRequest $request)
    {
        $currency = $this->currencyRepo->create($request->validated());
        if (!$currency) {
            return $this->responseService->json('Failed!', [], 401, ['error' => ['Failed to create currency']]);
        }
        $currency = new CurrencyResource($currency);
        return $this->responseService->json('Success!',  $currency, 201);
    }
}
