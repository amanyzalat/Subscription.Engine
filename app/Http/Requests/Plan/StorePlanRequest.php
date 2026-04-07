<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;


class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // In a real app, gate this to admin users
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => 'required|string|max:150',
            'description'        => 'required|string|max:1000',
            'trial_days'         => 'sometimes|integer|min:0|max:365',
            'is_active'          => 'sometimes|boolean',
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
