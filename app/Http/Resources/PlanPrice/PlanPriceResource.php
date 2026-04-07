<?php

namespace App\Http\Resources\PlanPrice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Currency\CurrencyResource;
use App\Http\Resources\Plan\PlanResource;
use App\Http\Resources\Billing\BillingResource;

class PlanPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'price'              => $this->price,
            'price_cents'        => $this->price_cents,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'billing_cycle' => new BillingResource($this->whenLoaded('billingCycle')),
            // 'created_at'         => $this->created_at->format('Y-m-d H:i:s'),
            //'updated_at'         => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
