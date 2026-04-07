<?php

namespace App\Http\Resources\Subscription;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Payment\PaymentResource;
use App\Http\Resources\PlanPrice\PlanPriceResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'user_id'               => $this->user_id,
            'price' => new PlanPriceResource($this->whenLoaded('price')),
            'status'                => $this->status,
            'has_access'            => $this->hasAccess(),

            // Billing window
            'current_period_start'  => $this->current_period_start?->format('Y-m-d H:i:s'),
            'current_period_end'    => $this->current_period_end?->format('Y-m-d H:i:s'),

            // Trial
            'trial_ends_at'         => $this->trial_ends_at?->format('Y-m-d H:i:s'),

            // Grace period
            'grace_period_ends_at'  => $this->grace_period_ends_at?->format('Y-m-d H:i:s'),

            // Cancellation
            'canceled_at'           => $this->canceled_at?->format('Y-m-d H:i:s'),
            'ends_at'               => $this->ends_at?->format('Y-m-d H:i:s'),



            'payments'              => PaymentResource::collection($this->whenLoaded('payments')),

            'created_at'            => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'            => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
