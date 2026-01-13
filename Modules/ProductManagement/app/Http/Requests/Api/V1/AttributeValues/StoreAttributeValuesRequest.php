<?php

namespace Modules\ProductManagement\Http\Requests\Api\V1\AttributeValues;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\ProductManagement\Models\AttributeValue;

class StoreAttributeValuesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', AttributeValue::class);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim strings
        $this->merge([
            'value' => $this->has('value') ? trim($this->input('value')) : null,
            'label' => $this->has('label') ? trim($this->input('label')) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'value' => [
                'required',
                'string',
                'max:255',
                // unique per attribute
                Rule::unique('attribute_values')->where(fn($query) =>
                    $query->where('attribute_id', $this->input('attribute_id'))
                ),
            ],
            'label' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'attribute_id.required' => 'The attribute field is required.',
            'attribute_id.integer'  => 'The attribute identifier must be an integer.',
            'attribute_id.exists'   => 'The selected attribute does not exist.',

            'value.required' => 'The value field is required.',
            'value.string'   => 'The value must be a string.',
            'value.max'      => 'The value must not exceed :max characters.',
            'value.unique'   => 'The value has already been taken for this attribute.',

            'label.string' => 'The label must be a string.',
            'label.max'    => 'The label must not exceed :max characters.',
        ];
    }

    /**
     * Human readable attribute names.
     */
    public function attributes(): array
    {
        return [
            'attribute_id' => 'attribute',
            'value'        => 'value',
            'label'        => 'label',
        ];
    }
}
