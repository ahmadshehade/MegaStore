<?php

namespace Modules\PaymentManagement\Http\Requests\Api\V1\PayementMethods;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Modules\PaymentManagement\Models\PaymentMethod;

class StorePaymentMethodRequest extends FormRequest
{
    /**
     * Summary of rules
     * @return array{code: string[], description: string[], image: string[], name: string[]}
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:25', 'unique:payment_methods,name'],
            'code' => [
                'required',
                'string',
                'min:6',
                'max:22',
                'regex:/^[A-Z0-9\-]+$/i',
                'unique:payment_methods,code',
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
        return Gate::allows('create', PaymentMethod::class);
    }

    /**
     * Summary of messages
     * @return array{image.image: string, image.max: string, image.mimes: string, max: string, min: string, regex: string, required: string, string: string, unique: string}
     */
    public function messages(): array
    {
        return [

            'required' => 'The :attribute field is required.',
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

    /**
     * Summary of attributes
     * @return array{code: string, description: string, image: string, name: string}
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
}
