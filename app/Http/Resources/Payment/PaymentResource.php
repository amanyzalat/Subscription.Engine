<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'subscription_id'   => $this->subscription_id,
            'status'            => $this->status,
            'amount_cents'      => $this->amount,
            'amount'            => $this->amount / 100,
            'currency'          => $this->currency,
            'payment_reference' => $this->transaction_id,
            'failure_reason'    => $this->failure_reason,
            'paid_at'           => $this->paid_at?->format('Y-m-d H:i:s'),
            'created_at'        => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
