<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSupplierRequest extends FormRequest
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
            'cnpj' => 'nullable|string|min:14|max:20|unique:suppliers,cnpj',
            'corporate_name' => 'nullable|string|max:250',
            'trade_name' => 'nullable|string|max:250',
            'email' => 'nullable|string|max:250|unique:suppliers,email',
            'cep' => 'nullable|string|max:12',
            'phone' => 'nullable|string|max:20|unique:suppliers,phone',
            'address_city' => 'nullable|string|max:150',
            'address_state' => 'nullable|string|max:50',
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
