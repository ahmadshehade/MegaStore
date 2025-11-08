<?php

namespace Modules\OrderManagement\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\OrderManagement\Models\Order;

class StoreOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['exists:users,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'shipping_id' => ['required', 'exists:shippings,id'],
            'status' => ['nullable', Rule::in(['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled', 'refunded'])],
            'notes' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Order::class);
    }

    /**
     * Summary of attributes
     * @return array{items: string, items.*.product_id: string, items.*.quantity: string, notes: string, payment_method_id: string, shipping_id: string, status: string, user_id: string}
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'user',
            'payment_method_id' => 'payment method',
            'shipping_id' => 'shipping method',
            'status' => 'order status',
            'notes' => 'order notes',
            'items' => 'order items',
            'items.*.product_id' => 'product',
            'items.*.quantity' => 'product quantity',
        ];
    }

    /**
     * Summary of messages
     * @return array{array: string, exists: string, max: string, min: array{array: string, numeric: string, required: string, required_with: string}}
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'exists' => 'The selected :attribute is invalid.',
            'array' => 'The :attribute must be an array.',
            'min' => [
                'array' => 'The :attribute must have at least one item.',
                'numeric' => 'The :attribute must be at least :min.',
            ],
            'max' => 'The :attribute may not be greater than :max characters.',
            'required_with' => 'The :attribute field is required when :values is present.',
        ];
    }

}
