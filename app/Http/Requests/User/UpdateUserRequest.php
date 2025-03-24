<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'nullable|string|max:250',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|min:4',
            'sidebar' => 'nullable|in:hover,open-lock,close-lock',
            'sector' => 'nullable|string|max:100',
            'registration' => 'nullable|min:1|max:5|unique:users,registration',
        ];

        if (auth()->guard()->user()->level !== 'operator') {
            $rules['level'] = 'nullable|in:admin,manager,operator';
            $rules['status'] = 'nullable|in:active,inactive';
        } else {
            unset($rules['email']);
        }
        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Os dados fornecidos são inválidos.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
