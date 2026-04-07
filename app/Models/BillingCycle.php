<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingCycle extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'months',
    ];

    public function prices()
    {
        return $this->hasMany(PlanPrice::class);
    }
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
}
