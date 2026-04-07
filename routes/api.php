<?php

use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SubscriptionPaymentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\BillingCycleController;
use App\Http\Controllers\Api\PlanPriceController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ----- Public routes -----
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login');
        Route::post('register', 'register');
    });
    //public plans
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/plans/{plan_id}', [PlanController::class, 'activePlansWithPrices']);
    //public plan prices
    Route::get('plan-prices', [PlanPriceController::class, 'index']);
    Route::get('plan-prices/{plan_price_id}', [PlanPriceController::class, 'show']);

    // ----- Authenticated routes -----
    Route::middleware('auth:sanctum')->group(function () {

        // -------- Admin Routes --------
        Route::middleware('admin')->prefix('admin')->group(function () {
            // Currencies
            Route::get('currencies', [CurrencyController::class, 'index']);
            Route::post('currencies', [CurrencyController::class, 'store']);
            Route::put('currencies/{currency}', [CurrencyController::class, 'update']);
            Route::delete('currencies/{currency}', [CurrencyController::class, 'destroy']);
            // Billing Cycles
            Route::get('billing', [BillingCycleController::class, 'index']);
            Route::post('billing', [BillingCycleController::class, 'store']);
            Route::put('billing/{billingCycle}', [BillingCycleController::class, 'update']);
            Route::delete('billing/{billingCycle}', [BillingCycleController::class, 'destroy']);

            // Plans management
            Route::get('plans', [PlanController::class, 'index']);
            Route::get('plans/{plan_id}', [PlanController::class, 'show']);
            Route::post('plans', [PlanController::class, 'store']);
            Route::put('plans/{plan_id}', [PlanController::class, 'update']);
            Route::delete('plans/{plan_id}', [PlanController::class, 'destroy']);
            // Plan Prices  management
            Route::get('plan-prices', [PlanPriceController::class, 'index']);
            Route::get('plan-prices/{plan_price_id}', [PlanPriceController::class, 'show']);
            Route::post('plan-prices', [PlanPriceController::class, 'store']);
            Route::put('plan-prices/{plan_price_id}', [PlanPriceController::class, 'update']);
            Route::delete('plan-prices/{plan_price_id}', [PlanPriceController::class, 'destroy']);
        });

        // -------- User Routes --------
        Route::middleware('user')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);

            // Subscriptions
            Route::get('subscriptions', [SubscriptionController::class, 'index']);
            Route::post('subscriptions', [SubscriptionController::class, 'store']);
            Route::get('subscriptions/{subscription}', [SubscriptionController::class, 'show']);
            Route::delete('subscriptions/{subscription}', [SubscriptionController::class, 'destroy']);
            Route::get('subscriptions/{subscription}/access', [SubscriptionController::class, 'checkAccess']);

            // Payments
            Route::get('subscriptions/{subscription}/payments', [SubscriptionPaymentController::class, 'index']);
            Route::post('subscriptions/{subscription}/payments/succeed', [SubscriptionPaymentController::class, 'succeed']);
            Route::post('subscriptions/{subscription}/payments/fail', [SubscriptionPaymentController::class, 'fail']);
        });
    });
});
