<?php

namespace Modules\ProductManagement\Http\Requests\Api\V1\Attributes;

use App\Enum\UserRoles;
use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\ProductManagement\Models\Attribute;

class StoreAttributeRequest extends BaseRequest
{
    public function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('name')) . Str::random(16),
        ]);
        if ($this->user()->hasRole(UserRoles::SuperAdmin->value)) {
            $this->merge([
                'scope' => 'global',
                'is_active' => true
            ]);
        }
        if ($this->user()->hasRole(UserRoles::Seller->value)) {
            $this->merge([
                'scope' => 'vendor',
                'is_active' => true,
                'created_by' => $this->user()->id
            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Attribute::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['string', 'max:255', Rule::unique('attributes', 'slug')],
            'type' => ['required', Rule::in(['select', 'text'])],
            'meta' => ['nullable', 'array'],
            'variant_values' => ['sometimes', 'array'],
            'variant_values.*' => ['nullable', 'integer', 'exists:product_variants,id'],

        ];

        if ($this->input('type') === 'select') {
            $rules['values'] = ['required', 'array', 'min:1'];
            $rules['values.*.value'] = ['required', 'string', 'max:255'];
            $rules['values.*.label'] = ['nullable', 'string', 'max:255'];
        }


        return $rules;
    }

    /**
     * Custom validation messages.
     */
   public function messages(): array
    {
        return [
            'name.required' => 'Please provide the attribute name.',
            'name.string'   => 'The attribute name must be text.',
            'name.max'      => 'The attribute name may not exceed :max characters.',
            'slug.unique'   => 'The attribute slug is already taken.',
            'type.required' => 'Please specify the attribute type.',
            'type.in'       => 'Invalid type, allowed values: select or text.',
            'values.required' => 'Values are required when type is select.',
            'values.array'  => 'Values must be provided as an array.',
            'values.*.value.required' => 'Each value must have a value field.',
            'values.*.value.string'   => 'Each value must be text.',
            'values.*.label.string'   => 'Each label must be text.',
            'variant_values.array' => 'variant_values must be an associative array of product_variant_id => attribute_value_id.',
            'variant_values.*.integer' => 'Each variant value must be an integer ID of attribute_value.',
            'variant_values.*.exists'  => 'One of the provided attribute_value ids does not exist.',
        ];
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
            'values.*.value' => 'Value',
            'values.*.label' => 'Label',
            'variant_values' => 'Variant Values mapping',
        ];
    }


     public function withValidator($validator)
    {
        $validator->after(function ($v) {
            if ($this->has('variant_values') && is_array($this->input('variant_values'))) {
                foreach (array_keys($this->input('variant_values')) as $variantId) {
                    if (!is_numeric($variantId) || ! DB::table('product_variants')->where('id', (int) $variantId)->exists()) {
                        $v->errors()->add('variant_values', "The product variant id [{$variantId}] is invalid or does not exist.");
                    }
                }
            }
            if ($this->input('type') === 'select') {
                $values = $this->input('values');
                if (!is_array($values) || count($values) < 1) {
                    $v->errors()->add('values', 'Values are required when type is select.');
                }
            }
        });
    }
}
