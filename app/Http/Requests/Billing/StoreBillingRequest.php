<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;


class StoreBillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // In a real app, gate this to admin users
        return true;
    }

    public function rules(): array
    {
        return ['name' => 'required|string|max:150', 'months' => 'required|integer|min:1'];
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
