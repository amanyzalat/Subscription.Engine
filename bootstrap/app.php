<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Console\Commands\ProcessSubscriptionLifecycle;
use Illuminate\Console\Scheduling\Schedule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exceptions\SubscriptionAlreadyActiveException;
use App\Exceptions\DuplicatePaymentReferenceException;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserMiddleware;
use App\Events\SubscriptionActivated;
use App\Events\PaymentFailed;
use App\Events\SubscriptionCanceled;
use App\Listeners\LogSubscriptionActivated;
use App\Listeners\SendSubscriptionActivatedNotification;
use App\Listeners\LogPaymentFailed;
use App\Listeners\SendPaymentFailedNotification;
use App\Listeners\LogSubscriptionCanceled;
use App\Listeners\LogSubscriptionCreated;
use App\Events\SubscriptionCreated;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command(ProcessSubscriptionLifecycle::class)
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->onFailure(function () {
                Log::error('subscriptions:process-lifecycle failed.');
            });
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'user' => UserMiddleware::class,
        ]);
    })
    ->withEvents([
        SubscriptionActivated::class => [
            LogSubscriptionActivated::class,
            SendSubscriptionActivatedNotification::class,
        ],
        PaymentFailed::class => [
            LogPaymentFailed::class,
            SendPaymentFailedNotification::class,
        ],
        SubscriptionCanceled::class => [
            LogSubscriptionCanceled::class
        ],
        SubscriptionCreated::class => [
            LogSubscriptionCreated::class
        ],
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (SubscriptionAlreadyActiveException $e) {
            return response()->json([
                "status" => "Fail!",
                "data" => [],
                "pagination" => null,
                "message" => $e->getMessage(),
                "errors" => [$e->getMessage()]
            ]);
        });
        $exceptions->render(function (DuplicatePaymentReferenceException $e) {
            return response()->json([
                "status" => "Fail!",
                "data" => [],
                "pagination" => null,
                "message" => $e->getMessage(),
                "errors" => [$e->getMessage()]
            ]);
        });
        $exceptions->render(function (AccessDeniedHttpException $e) {
            return response()->json([
                "status" => "Fail!",
                "data" => [],
                "pagination" => null,
                "message" => "You do not have the required authorization.",
                "errors" => ['You do not have the required authorization.']
            ]);
        });
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                "status" => "Fail!",
                "data" => [],
                "pagination" => null,
                "message" => $e->getMessage(),
                "errors" => [$e->getMessage()]
            ]);
        });
    })->create();
