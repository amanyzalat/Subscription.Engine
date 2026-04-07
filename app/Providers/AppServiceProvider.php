<?php

namespace App\Providers;

use App\Models\Subscription;
use App\Policies\SubscriptionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Http\Repositories\Base\BaseInterface;
use App\Http\Repositories\Base\BaseRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        $this->app->bind(BaseInterface::class, BaseRepository::class);
    }
}
