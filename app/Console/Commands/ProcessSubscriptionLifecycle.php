<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptionLifecycle extends Command
{
    protected $signature   = 'subscriptions:process-lifecycle';
    protected $description = 'Expire trials and cancel subscriptions past their grace period.';

    public function __construct(private readonly SubscriptionService $subscriptionService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('[1/2] Expiring trials...');
        $expired = $this->subscriptionService->expireTrials();
        $this->line("  → {$expired} trial(s) transitioned.");

        $this->info('[2/2] Canceling expired grace periods...');
        $canceled = $this->subscriptionService->cancelExpiredGracePeriods();
        $this->line("  → {$canceled} subscription(s) auto-canceled.");

        $this->info('Done.');

        return self::SUCCESS;
    }
}
