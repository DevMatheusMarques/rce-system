<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends FormRequest
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
            'name' => 'nullable|string|max:50|unique:products',
            'description' => 'nullable|string|max:400',
            'category' => 'nullable|in:toner,paper,form,cartridge,ribbon,others,desk',
            'minimum' => 'nullable|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'status' => 'nullable|in:active,inactive'
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
