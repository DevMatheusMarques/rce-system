<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterUserRequest extends FormRequest
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
            'name' => 'required|string|max:250',
            'email' => 'required|email|unique:users,email',
            'status' => 'required:in:active,inactive',
            'password' => 'nullable|min:4',
            'registration' => 'nullable|string|min:1|max:5|unique:users,registration',
            'sector' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
        ];

        if (auth()->guard()->user()->level == 'admin') {
            $rules['level'] = 'required|in:admin,manager,operator';
        } else if (auth()->guard()->user()->level == 'manager') {
            $rules['level'] = 'required|in:manager,operator';
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
