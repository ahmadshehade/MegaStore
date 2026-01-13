<?php

namespace Modules\OrderManagement\Http\Requests\Api\V1\Discount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Modules\OrderManagement\Rules\ValidDiscountValue;

class StoreDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {

        return Gate::allows('admin-job');
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
                'max:16',
                'unique:discounts,code',
            ],

            'type' => [
                'required',
                'string',
                'in:percentage,fixed',
            ],

            'value' => [
                'required',
                'numeric',
                 new ValidDiscountValue($this->input('type'))
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
                'min:10',
                'max:255',
            ],

            'start_date' => [
                'sometimes',
                'nullable',
                'date',
                'before_or_equal:end_date',
            ],

            'end_date' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'code.min' => 'The discount code must be at least 8 characters.',
            'code.max' => 'The discount code may not exceed 16 characters.',
            'code.unique' => 'This discount code is already in use.',

            'type.required' => 'Discount type is required.',
            'type.in' => 'Discount type must be either "percentage" or "fixed".',

            'value.required' => 'Discount value is required.',
            'value.numeric' => 'Discount value must be numeric.',
            'value.min' => 'Discount value must be at least 0.',
            'value.max' => 'Discount value is too large.',

            'description.min' => 'Description must be at least 10 characters.',
            'description.max' => 'Description may not exceed 255 characters.',

            'start_date.date' => 'Start date must be a valid date.',
            'start_date.before_or_equal' => 'Start date must be before or equal to the end date.',

            'end_date.date' => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be after or equal to the start date.',

            'status.boolean' => 'Status must be true or false.',
        ];
    }

    /**
     * Attribute names
     */
    public function attributes(): array
    {
        return [
            'code' => 'discount code',
            'type' => 'discount type',
            'value' => 'discount value',
            'description' => 'description',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'status' => 'status',
        ];
    }
}
