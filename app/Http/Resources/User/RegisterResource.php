<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'email'               => $this->email,
            'updated_at'      => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at'           => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
