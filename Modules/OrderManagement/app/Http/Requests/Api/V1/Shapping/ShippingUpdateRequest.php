<?php

namespace Modules\OrderManagement\Http\Requests\Api\V1\Shapping;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\OrderManagement\Models\Shipping;

class ShippingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('shipping'));
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }

        if ($this->filled('cost')) {
            $this->merge([
                'cost' => str_replace(',', '.', $this->input('cost')),
            ]);
        }
    }

    public function rules(): array
    {
        $shipping = $this->route('shipping');

        return [
            'name'      => ['sometimes', 'string', 'min:2', 'max:122', Rule::unique('shippings', 'name')->ignore($shipping->id)],
            'day_id'    => ['sometimes', 'integer', 'exists:days,id'],
            'cost'      => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'image'     => ['sometimes', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string'    => ':attribute must be a string.',
            'name.min'       => ':attribute must be at least :min characters.',
            'name.max'       => ':attribute must not exceed :max characters.',
            'name.unique'    => ':attribute has already been taken.',

            'day_id.integer' => 'The :attribute must be an integer.',
            'day_id.exists'  => 'The selected :attribute is invalid.',

            'cost.numeric'   => 'The :attribute must be a valid number.',
            'cost.min'       => 'The :attribute must be at least :min.',

            'is_active.boolean' => 'The :attribute must be true or false.',

            'image.image'    => 'The :attribute must be an image file.',
            'image.mimes'    => 'Allowed image types: jpeg, jpg, png, webp, gif.',
            'image.max'      => 'The :attribute size must not exceed 5 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'      => 'Shipping Name',
            'day_id'    => 'Day',
            'cost'      => 'Cost',
            'is_active' => 'Activation Status',
            'image'     => 'Shipping Image',
        ];
    }
}
