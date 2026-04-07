<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'code',
        'symbol',
    ];

    public function prices()
    {
        return $this->hasMany(PlanPrice::class);
    }
}
