<?php

namespace App\Http\Requests\Currency;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;


class StoreCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // In a real app, gate this to admin users
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:currencies,code',
            'symbol' => 'required|string',
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
