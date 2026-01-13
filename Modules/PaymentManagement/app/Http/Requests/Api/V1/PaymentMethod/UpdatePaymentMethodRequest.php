<?php

namespace Modules\PaymentManagement\Http\Requests\Api\V1\PaymentMethod;

use App\Rules\Decimal102Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
       return [
            'name' => [
                'sometimes',
                'string',
                'min:2',
                'max:255',
                Rule::unique('payment_methods')->ignore($this->route('paymentMethod')->id)
            ],
            'description' => ['sometimes', 'string', 'min:10', 'max:255'],
            'type' => ['sometimes', 'string', 'min:4', 'max:255'],
            'fee' => ['sometimes','numeric', 'between:0.00,999999999.99', new Decimal102Rule()],
            'security_features' => ['sometimes', 'nullable', 'string'],
            'integration_details' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('admin-job');
    }


        /**
         * Summary of messages
         * @return array{description.max: string, description.min: string, description.string: string, fee.between: string, fee.numeric: string, integration_details.string: string, is_active.boolean: string, name.max: string, name.min: string, name.required: string, name.string: string, name.unique: string, security_features.string: string, type.max: string, type.min: string, type.required: string, type.string: string}
         */
        public function messages(): array
    {
        return [

            'name.string' => 'The payment method name must be a string.',
            'name.min' => 'The payment method name must be at least :min characters.',
            'name.max' => 'The payment method name may not be greater than :max characters.',
            'name.unique' => 'This payment method name is already taken.',

            'description.string' => 'Description must be a string.',
            'description.min' => 'Description must be at least :min characters.',
            'description.max' => 'Description may not be greater than :max characters.',


            'type.string' => 'Type must be a string.',
            'type.min' => 'Type must be at least :min characters.',
            'type.max' => 'Type may not be greater than :max characters.',

            'fee.numeric' => 'Fee must be a number.',
            'fee.between' => 'Fee must be between :min and :max.',
            'is_active.boolean' => 'The is_active field must be true or false.',

            'security_features.string' => 'Security features must be a string.',
            'integration_details.string' => 'Integration details must be a string.',
        ];
    }

       /**
        * Summary of attributes
        * @return array{description: string, fee: string, integration_details: string, is_active: string, name: string, security_features: string, type: string}
        */
       public function attributes(): array
    {
        return [
            'name' => 'payment method name',
            'description' => 'description',
            'type' => 'type',
            'fee' => 'fee',
            'security_features' => 'security features',
            'integration_details' => 'integration details',
            'is_active' => 'activation status',
        ];
    }
}
