<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('plan_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('price_id')
                ->constrained('plan_prices')
                ->restrictOnDelete();
            $table->enum('status', [
                'trialing',
                'active',
                'past_due',
                'canceled',
            ])->default('trialing');
            // Billing period
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            // Grace period tracking
            $table->timestamp('grace_period_ends_at')->nullable();
            // Cancellation
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ends_at')->nullable(); // When access truly expires

            $table->timestamps();
            $table->softDeletes();
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'trial_ends_at']);
            $table->index(['status', 'grace_period_ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
