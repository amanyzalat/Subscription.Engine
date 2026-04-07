<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\PaymentStatus;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'user_id',
        'amount',
        'currency',
        'status',
        'transaction_id',
        'failure_reason',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Status Helpers
    // -------------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status ===  PaymentStatus::PENDING->value;
    }

    public function isSucceeded(): bool
    {
        return $this->status === PaymentStatus::SUCCEEDED->value;
    }

    public function isFailed(): bool
    {
        return $this->status ===  PaymentStatus::FAILED->value;
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Formatted amount string, e.g. "USD 9.99".
     */
    public function getFormattedAmountAttribute(): string
    {
        return strtoupper($this->currency) . ' ' . number_format($this->amount, 2);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::PENDING->value);
    }

    public function scopeSucceeded($query)
    {
        return $query->where('status', PaymentStatus::SUCCEEDED->value);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', PaymentStatus::FAILED->value);
    }
}
