<?php

namespace App\Repositories\Currency;

use App\Repositories\Base\BaseRepository;
use App\Models\Currency;

class CurrencyRepository extends BaseRepository
{
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }
}
