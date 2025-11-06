<?php

namespace Modules\OrderManagement\Http\Requests\Api\V1\Shapping;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\OrderManagement\Models\Shipping;

class ShippingStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Shipping::class);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize cost (e.g., handle commas from frontend input)
        if ($this->filled('cost')) {
            $this->merge([
                'cost' => str_replace(',', '.', $this->input('cost')),
            ]);
        }

        // Normalize boolean-like is_active
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:122', Rule::unique('shippings', 'name')],
            'day_id' => ['required', 'integer', 'exists:days,id'],
            'cost' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5000'],
        ];
    }

    /**
     * Custom messages in English
     */
    public function messages(): array
    {
        return [
            'name.required' => ':attribute is required.',
            'name.string' => ':attribute must be a string.',
            'name.min' => ':attribute must be at least :min characters.',
            'name.max' => ':attribute must not exceed :max characters.',
            'name.unique' => ':attribute has already been taken.',

            'day_id.required' => 'The :attribute is required.',
            'day_id.integer' => 'The :attribute must be an integer.',
            'day_id.exists' => 'The selected :attribute is invalid.',

            'cost.required' => 'The :attribute is required.',
            'cost.numeric' => 'The :attribute must be a valid number.',
            'cost.min' => 'The :attribute must be at least :min.',

            'is_active.boolean' => 'The :attribute must be true or false.',

            'image.image' => 'The :attribute must be an image file.',
            'image.mimes' => 'The allowed image types are: jpeg, jpg, png, webp, gif.',
            'image.max' => 'The :attribute size must not exceed 5 MB.',
        ];
    }

    /**
     * Custom attribute names in English
     */
    public function attributes(): array
    {
        return [
            'name' => 'Shipping Name',
            'day_id' => 'Day',
            'cost' => 'Cost',
            'is_active' => 'Activation Status',
            'image' => 'Shipping Image',
        ];
    }
}
