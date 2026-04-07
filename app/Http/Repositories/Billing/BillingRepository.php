<?php

namespace App\Http\Repositories\Billing;

use App\Http\Repositories\Base\BaseRepository;
use App\Models\BillingCycle;

class BillingRepository extends BaseRepository
{
    public function __construct(BillingCycle $model)
    {
        parent::__construct($model);
    }
}
