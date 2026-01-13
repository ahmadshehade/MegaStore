<?php

namespace Modules\ProductManagement\Http\Requests\Api\V1\AttributeValues;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\ProductManagement\Models\AttributeValue;

class UpdateAttributeValuesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('attributeValue'));
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {

        if ($this->filled('value')) {
            $this->merge([
                'value' => trim($this->input('value')),
            ]);
        }
        if ($this->filled('label')) {
            $this->merge([
                'label' => trim($this->input('label')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'attribute_id' => ['sometimes', 'integer', 'exists:attributes,id'],
            'value' => [
                'sometimes',
                'string',
                'max:255',

                Rule::unique('attribute_values')
                    ->where(fn($query) => $query->where('attribute_id', $this->input('attribute_id', $this->route('attributeValue')->attribute_id)))
                    ->ignore($this->route('attributeValue')->id)
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

            'attribute_id.integer'  => 'The attribute identifier must be an integer.',
            'attribute_id.exists'   => 'The selected attribute does not exist.',

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
