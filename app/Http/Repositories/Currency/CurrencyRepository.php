<?php

namespace App\Http\Repositories\Currency;

use App\Http\Repositories\Base\BaseRepository;
use App\Models\Currency;

class CurrencyRepository extends BaseRepository
{
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }
}
