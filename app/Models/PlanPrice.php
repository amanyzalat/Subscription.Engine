<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plan_id',
        'price',
        'price_cents',
        'currency_id',
        'billing_cycle_id',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'price_cents' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The plan this price belongs to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function billingCycle(): BelongsTo
    {
        return $this->belongsTo(BillingCycle::class);
    }
    /**
     * Subscriptions using this specific price.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'price_id');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Formatted price string, e.g. "USD 9.99".
     */
    public function getFormattedPriceAttribute(): string
    {
        $currency = $this->currency;
        if (!$currency) {
            return number_format($this->price, 2);
        }
        return "{$currency->code} " . number_format($this->price, 2);
    }

    /**
     * Return price as a float calculated from cents (e.g. 999 → 9.99).
     */
    public function getPriceFromCentsAttribute(): float
    {
        return $this->price_cents / 100;
    }
}
