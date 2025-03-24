<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterSupplierRequest extends FormRequest
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
        return [
            'cnpj' => 'required|string|min:14|max:20|unique:suppliers,cnpj',
            'corporate_name' => 'required|string|max:250',
            'trade_name' => 'nullable|string|max:250',
            'email' => 'required|string|max:250|unique:suppliers,email',
            'cep' => 'required|string|max:12',
            'phone' => 'required|string|max:20|unique:suppliers,phone',
            'address_city' => 'required|string|max:150',
            'address_state' => 'required|string|max:50',
        ];
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
