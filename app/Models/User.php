<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('current_period_end', '>', now());
    }

    public function hasActiveSubscription()
    {
        return $this->activeSubscription() !== null;
    }

    public function hasGracePeriodSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'grace_period')
            ->where('current_period_end', '>', now());
    }

    public function hasExpiredSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'expired')
            ->where('current_period_end', '<', now());
    }

    public function hasValidSubscription()
    {
        return $this->activeSubscription() || $this->hasGracePeriodSubscription();
    }

    public function getSubscriptionStatusAttribute()
    {
        if ($this->activeSubscription()) {
            return 'active';
        }

        if ($this->gracePeriodSubscription()) {
            return 'grace_period';
        }

        if ($this->expiredSubscription()) {
            return 'expired';
        }

        return 'inactive';
    }

    public function getSubscriptionPlanAttribute()
    {
        return $this->activeSubscription()?->plan;
    }

    public function getSubscriptionPriceAttribute()
    {
        return $this->activeSubscription()?->planPrice;
    }

    public function getSubscriptionBillingCycleAttribute()
    {
        return $this->activeSubscription()?->billingCycle;
    }

    public function getSubscriptionCurrencyAttribute()
    {
        return $this->activeSubscription()?->currency;
    }


    public function getSubscriptionCurrentPeriodStartAttribute()
    {
        return $this->activeSubscription()?->current_period_start;
    }

    public function getSubscriptionCurrentPeriodEndAttribute()
    {
        return $this->activeSubscription()?->current_period_end;
    }

    public function getSubscriptionCancelAtAttribute()
    {
        return $this->activeSubscription()?->cancel_at;
    }

    public function getSubscriptionCanceledAtAttribute()
    {
        return $this->activeSubscription()?->canceled_at;
    }

    public function getSubscriptionRenewsAtAttribute()
    {
        return $this->activeSubscription()?->renews_at;
    }

    public function getSubscriptionGracePeriodUntilAttribute()
    {
        return $this->activeSubscription()?->grace_period_until;
    }

    public function getSubscriptionGracePeriodDaysAttribute()
    {
        return $this->activeSubscription()?->grace_period_days;
    }

    public function getSubscriptionGracePeriodRemainingDaysAttribute()
    {
        return $this->activeSubscription()?->grace_period_remaining_days;
    }

    public function getSubscriptionGracePeriodExpiredAttribute()
    {
        return $this->activeSubscription()?->grace_period_expired;
    }

    public function getSubscriptionGracePeriodActiveAttribute()
    {
        return $this->activeSubscription()?->grace_period_active;
    }

    public function getSubscriptionGracePeriodRemainingAttribute()
    {
        return $this->activeSubscription()?->grace_period_remaining;
    }
}
