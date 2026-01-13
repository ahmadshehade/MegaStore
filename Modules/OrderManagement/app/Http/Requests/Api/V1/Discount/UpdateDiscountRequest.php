<?php

namespace Modules\OrderManagement\Http\Requests\Api\V1\Discount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Modules\OrderManagement\Rules\ValidDiscountValue;

class UpdateDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow only users with admin-job permission
        return Gate::allows('admin-job');
    }

    public function rules(): array
    {

        $discount = $this->route('discount');

        return [
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
                'max:16',
                'unique:discounts,code,' . $discount->id,
            ],

            'type' => [
                'sometimes',
                'string',
                'in:percentage,fixed',
            ],

            'value' => [
                'sometimes',
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

    public function messages(): array
    {
        return [
            'code.min' => 'The discount code must be at least 8 characters.',
            'code.max' => 'The discount code may not exceed 16 characters.',
            'code.unique' => 'This discount code is already in use.',

            'type.in' => 'Discount type must be either "percentage" or "fixed".',

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
