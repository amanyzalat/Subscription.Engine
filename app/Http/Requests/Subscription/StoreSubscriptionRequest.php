<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\PlanPrice;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id'  => ['required', Rule::exists('plans', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'price_id' => [
                'required',
                'integer',
                Rule::exists('plan_prices', 'id')->whereNull('deleted_at'),
                // Ensure the price belongs to the chosen plan
                function ($attribute, $value, $fail) {
                    $planId = $this->input('plan_id');
                    if ($planId && !PlanPrice::where('id', $value)->where('plan_id', $planId)->exists()) {
                        $fail('The selected price does not belong to the chosen plan.');
                    }
                },
            ],
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        $allMessages = [];
        foreach ($errors as $field => $messages) {
            $allMessages = array_merge($allMessages, $messages);
        }

        throw new HttpResponseException(response()->json([
            'status' => 'fail',
            'data' => [],
            'pagination' => null,
            'message' => $allMessages[0] ?? 'Validation error',
            'errors' => $allMessages,

        ], 422));
    }
}
