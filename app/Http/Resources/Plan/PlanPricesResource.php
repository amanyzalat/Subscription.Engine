<?php

namespace App\Http\Resources\Plan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PlanPrice\PlanPriceResource;

class PlanPricesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'slug'               => $this->slug,
            'description'        => $this->description,
            'trial_days'         => $this->trial_days,
            'has_trial'          => $this->hasTrialPeriod(),
            'is_active'          => (bool)$this->is_active,
            'prices' => PlanPriceResource::collection($this->whenLoaded('prices')),
            // 'created_at'         => $this->created_at->format('Y-m-d H:i:s'),
            // 'updated_at'         => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
