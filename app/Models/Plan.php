<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Plan extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'trial_days',
        'is_active',
    ];

    protected $casts = [
        'trial_days' => 'integer',
        'is_active'  => 'boolean',
    ];
    /**
     * Boot method to generate slug automatically
     */
    protected static function boot()
    {
        parent::boot();


        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = self::generateUniqueSlug($model->name);
            }
        });
    }

    /**
     * Generate a unique slug
     */
    protected static function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function prices(): HasMany
    {
        return $this->hasMany(PlanPrice::class);
    }
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function hasTrialPeriod(): bool
    {
        return $this->trial_days > 0;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
