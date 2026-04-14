<?php

namespace App\Repositories\Billing;

use App\Repositories\Base\BaseRepository;
use App\Models\BillingCycle;

class BillingRepository extends BaseRepository
{
    public function __construct(BillingCycle $model)
    {
        parent::__construct($model);
    }
}
