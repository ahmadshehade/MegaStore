<?php

namespace Modules\ProductManagement\Http\Requests\Api\V1\Attributes;

use App\Enum\UserRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\ProductManagement\Models\Attribute;

class UpdateAttributeRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        if ($this->filled('name')) {
            $this->merge([
                'slug' => Str::slug($this->input('name')) . Str::random(8),
            ]);
        }
        if ($this->user()->hasRole(UserRoles::SuperAdmin->value)) {
            $this->merge([
                'scope' => 'global',
                'created_by' => null,

            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('attribute'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('attributes', 'slug')->ignore($this->route('attribute')->id)],
            'type' => ['sometimes', Rule::in(['select', 'text'])],
            'meta' => ['nullable', 'array'],
            'variant_values' => ['sometimes', 'array'],
            'variant_values.*' => ['nullable', 'integer', 'exists:product_variants,id'],
        ];

        if ($this->input('type') === 'select') {
            $rules['values'] = ['required', 'array', 'min:1'];
            $rules['values.*.id'] = ['sometimes', 'integer', 'exists:attribute_values,id'];
            $rules['values.*.value'] = ['required', 'string', 'max:255'];
            $rules['values.*.label'] = ['nullable', 'string', 'max:255'];
        }
        if ($this->user()->hasRole(UserRoles::SuperAdmin->value)) {
            $rules['is_active'] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Attribute Name',
            'slug' => 'Slug',
            'type' => 'Type',
            'meta' => 'Metadata',
            'values' => 'Attribute Values',
            'values.*.id' => 'Value ID',
            'values.*.value' => 'Value',
            'values.*.label' => 'Label',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.string'   => 'The attribute name must be a valid text string.',
            'name.min'      => 'The attribute name must contain at least :min characters.',
            'name.max'      => 'The attribute name may not exceed :max characters.',

            'slug.string'   => 'The slug must be a valid string.',
            'slug.max'      => 'The slug may not exceed :max characters.',
            'slug.unique'   => 'The slug must be unique. Another attribute with the same slug already exists.',

            'type.in'       => 'Invalid type selected. Allowed types are: select or text.',
            'type.required' => 'The attribute type is required.',

            'meta.array'    => 'The metadata field must be a valid array.',

            'values.required' => 'Values are required when the attribute type is set to select.',
            'values.array'    => 'Values must be provided as an array.',
            'values.min'      => 'At least one value must be provided for select attributes.',

            'values.*.id.integer' => 'Each value ID must be a valid integer.',
            'values.*.id.exists'  => 'One of the provided value IDs does not exist.',

            'values.*.value.required' => 'Each attribute value must contain a value field.',
            'values.*.value.string'   => 'Each attribute value must be a valid text string.',
            'values.*.value.max'      => 'Each attribute value may not exceed :max characters.',

            'values.*.label.string' => 'Each label must be a valid text string.',
            'values.*.label.max'    => 'Each label may not exceed :max characters.',

            'is_active.boolean' => 'The active status must be true or false.',
            
            'variant_values.array' => 'variant_values must be an associative array of product_variant_id => attribute_value_id.',
            'variant_values.*.integer' => 'Each variant value must be an integer ID of attribute_value.',
            'variant_values.*.exists'  => 'One of the provided attribute_value ids does not exist.',

        ];
    }
}
