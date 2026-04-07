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
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('price_cents'); // Store in smallest unit
            $table->foreignId('billing_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->unique(['plan_id', 'currency_id', 'billing_cycle_id']);
            $table->timestamps();
            // Indexes 
            $table->index(['currency_id', 'billing_cycle_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
    }
};
