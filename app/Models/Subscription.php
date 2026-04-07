<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\SubscriptionStatus;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'plan_id',
        'price_id',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'grace_period_ends_at',
        'canceled_at',
        'ends_at',
    ];

    protected $casts = [
        'trial_ends_at'        => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'canceled_at'          => 'datetime',
        'ends_at'              => 'datetime',
        'status' => SubscriptionStatus::class
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(PlanPrice::class, 'price_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }


    // -------------------------------------------------------------------------
    // Status helpers
    // -------------------------------------------------------------------------

    public function isTrialing(): bool
    {
        return $this->status === SubscriptionStatus::TRIALING->value;
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE->value;
    }

    public function isPastDue(): bool
    {
        return $this->status === SubscriptionStatus::PAST_DUE->value;
    }

    public function isCanceled(): bool
    {
        return $this->status === SubscriptionStatus::CANCELED->value;
    }
    /**
     * User should still have access during trial, active, or grace period.
     */
    public function hasAccess(): bool
    {
        if ($this->isCanceled()) {
            return false;
        }

        if ($this->isTrialing() && now()->lessThanOrEqualTo($this->trial_ends_at)) {
            return true;
        }

        if ($this->isActive()) {
            return true;
        }

        // Past due but still within grace period
        if ($this->isPastDue() && $this->grace_period_ends_at && now()->lessThanOrEqualTo($this->grace_period_ends_at)) {
            return true;
        }

        return false;
    }


    public function onGracePeriod(): bool
    {
        return $this->isCanceled()
            && $this->grace_period_ends_at?->isFuture();
    }

    public function hasExpired(): bool
    {
        return $this->ends_at?->isPast() ?? false;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE->value);
    }

    public function scopeTrialing($query)
    {
        return $query->where('status', SubscriptionStatus::TRIALING->value);
    }

    public function scopePastDue($query)
    {
        return $query->where('status', SubscriptionStatus::PAST_DUE->value);
    }
    public function scopeCanceled($query)
    {
        return $query->where('status', SubscriptionStatus::CANCELED->value);
    }

    public function scopeExpiredTrials($query)
    {
        return $query->trialing()->where('trial_ends_at', '<=', now());
    }

    public function scopeExpiredGracePeriods($query)
    {
        return $query->pastDue()->where('grace_period_ends_at', '<=', now());
    }
}
