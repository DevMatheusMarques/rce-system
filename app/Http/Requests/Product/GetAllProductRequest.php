<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetAllProductRequest extends FormRequest
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
            'per_page' => 'required|integer|min:1',
            'order_by' => 'required|string|in:name,description,category,minimum,stock,processing,status,created_at,updated_at',
            'order_direction' => 'required|string|in:asc,desc',
            'filters' => 'nullable|array',
            'filters.status' => 'nullable|string|in:active,inactive',
            'filters.search' => 'nullable|string|max:255',
            'filters.in_stock' => 'nullable|boolean',
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
