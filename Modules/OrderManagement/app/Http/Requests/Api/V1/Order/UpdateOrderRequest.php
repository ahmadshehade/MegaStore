<?php

namespace Modules\OrderManagement\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Uses OrderPolicy update method
        return Gate::allows('update', $this->route('order'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'payment_method_id' => ['sometimes', 'required', 'exists:payment_methods,id'],
            'shipping_id' => ['sometimes', 'required', 'exists:shippings,id'],
            'status' => [
                'sometimes',
                Rule::in([
                    'pending',
                    'paid',
                    'processing',
                    'shipped',
                    'completed',
                    'cancelled',
                    'refunded'
                ])
            ],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],

            'items' => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
             'address_id'=>['integer','exists:addresses,id']

        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'payment_method_id' => 'payment method',
            'shipping_id' => 'shipping method',
            'status' => 'order status',
            'notes' => 'order notes',
            'items' => 'order items',
            'items.*.product_id' => 'product',
            'items.*.quantity' => 'product quantity',
            'address_id'=>'Address'

        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'required_with' => 'The :attribute field is required when :values is present.',
            'exists' => 'The selected :attribute is invalid.',
            'array' => 'The :attribute must be an array.',
            'min.array' => 'The :attribute must have at least :min item(s).',
            'min.numeric' => 'The :attribute must be at least :min.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'integer'=>'The :attribute must be integer .'
        ];
    }
}
