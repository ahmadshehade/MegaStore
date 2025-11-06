<?php

namespace Modules\PaymentManagement\Http\Requests\Api\V1\PayementMethods;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\PaymentManagement\Models\PaymentMethod;

class UpdatePaymentMethodRequest extends FormRequest
{

    /**
     * Summary of rules
     * @return array{code: array<string|\Illuminate\Validation\Rules\Unique>, description: string[], image: string[], name: array<string|\Illuminate\Validation\Rules\Unique>}
     */
    public function rules(): array
    {
        $paymentMethod = $this->route("paymentMethod");

        return [
            'name' => [
                'sometimes',
                'string',
                'min:3',
                Rule::unique('payment_methods')->ignore($paymentMethod->id)
            ],
            'code' => [
                'sometimes',
                'string',
                'min:6',
                'max:22',
                'regex:/^[A-Z0-9\-]+$/i',
                Rule::unique('payment_methods')->ignore($paymentMethod->id)
            ],
            'description' => ['sometimes', 'string', 'min:10', 'max:30'],
            'image' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Summary of authorize
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('update',PaymentMethod::class);
    }

    /**
     * Custom attribute names
     */
    public function attributes(): array
    {
        return [
            'name' => 'payment method name',
            'code' => 'payment method code',
            'description' => 'description',
            'image' => 'image',
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [

            'string' => 'The :attribute must be a valid string.',
            'min' => 'The :attribute must be at least :min characters.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'unique' => 'This :attribute already exists.',
            'regex' => 'The :attribute may only contain letters, numbers, and hyphens.',
            'image.image' => 'The :attribute must be an image.',
            'image.mimes' => 'The :attribute must be a file of type: jpg, jpeg, png, webp.',
            'image.max' => 'The :attribute must not exceed 2MB.',
        ];
    }
}
