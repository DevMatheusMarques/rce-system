<?php

namespace App\Http\Requests\OrderItem;

use App\Models\OrderItem;
use App\Rules\CombinedUniqueFieldsRule;
use App\Rules\CopyCombinedUniqueFieldsRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterOrderItemRequest extends FormRequest
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
            'quantity_product' => 'required|numeric|min:1',
            /**
             * Verifica se é único a composição do campo 'order_id' e 'product_id'.
             */
            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
                new CombinedUniqueFieldsRule(
                    new OrderItem(),
                    'product_id',
                    $this->input('product_id')
                )
            ],
            'product_id' => 'required|integer|exists:products,id'
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
