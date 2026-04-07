<?php

namespace App\Http\Requests\PlanPrice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePlanPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // In a real app, gate this to admin users
        return true;
    }

    public function rules(): array
    {
        return [
            'price'              => ['required', 'numeric', 'min:0'],
            'currency_id'        => ['required', Rule::exists('currencies', 'id')->whereNull('deleted_at')],
            'plan_id'            => ['required', Rule::exists('plans', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'billing_cycle_id'   => [
                'required',
                Rule::exists('billing_cycles', 'id')->whereNull('deleted_at'),
                Rule::unique('plan_prices')->where(function ($query) {
                    return $query
                        ->where('plan_id', $this->plan_id)
                        ->where('currency_id', $this->currency_id)
                        ->where('billing_cycle_id', $this->billing_cycle_id);
                }),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'billing_cycle_id.unique' => 'This plan already has a price for this currency and billing cycle.',
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
