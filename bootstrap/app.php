<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Console\Commands\ProcessSubscriptionLifecycle;
use Illuminate\Console\Scheduling\Schedule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
                \Illuminate\Support\Facades\Log::error('subscriptions:process-lifecycle failed.');
            });
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'user' => \App\Http\Middleware\UserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
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
