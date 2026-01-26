<?php

namespace Modules\ProductManagement\Http\Requests\Api\V1\ProductVariants;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\ProductManagement\Rules\Decimal122Rule;
use Modules\ProductManagement\Rules\Decimal82Rule;

class UpdateProductVariantsRequest extends BaseRequest
{

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        $variantId = $this->route('productvariant')->id ?? null;

        return [
            'product_id'          => ['sometimes', 'integer', 'exists:products,id'],

            'sku'                 => array_filter([
                'sometimes',
                'string',
                'min:5',
                'max:32',
                $variantId
                    ? Rule::unique('product_variants', 'sku')->ignore($variantId)
                    : Rule::unique('product_variants', 'sku'),
            ]),

            'price'               => ['sometimes', new Decimal122Rule],
            'compare_price'       => ['sometimes', 'nullable', new Decimal122Rule],

            'stock_quantity'      => ['sometimes', 'integer', 'min:0'],
            'low_stock_threshold' => ['sometimes', 'nullable', 'integer', 'min:0'],

            'weight'              => ['sometimes', 'nullable', new Decimal82Rule],
            'is_active'           => ['sometimes', 'boolean'],

            'images'              => ['sometimes', 'array'],
            'images.*'            => ['nullable', 'file', 'image', 'max:5120'],
        ];
    }

    /**
     * Authorization.
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('productvariant'));
    }

    /**
     * Custom messages.
     */
    public function messages(): array
    {
        return [
            'product_id.exists'            => 'Selected product does not exist.',
            'sku.unique'                   => 'This SKU is already in use.',
            'sku.min'                      => 'SKU must be at least :min characters.',
            'sku.max'                      => 'SKU must not exceed :max characters.',

            'stock_quantity.integer'       => 'Stock quantity must be an integer.',
            'stock_quantity.min'           => 'Stock quantity must be at least :min.',
            'low_stock_threshold.integer'  => 'Low stock threshold must be an integer.',
            'low_stock_threshold.min'      => 'Low stock threshold must be at least :min.',

            'images.array'                 => 'Images must be provided as an array.',
            'images.*.file'                => 'Each image must be a valid uploaded file.',
            'images.*.image'               => 'Each uploaded file must be an image.',
            'images.*.max'                 => 'Each image must be smaller than :max kilobytes (5MB).',
        ];
    }

    /**
     * Attribute names for clearer error messages.
     */
    public function attributes(): array
    {
        return [
            'product_id'          => 'Product ID',
            'sku'                 => 'SKU',
            'price'               => 'Price',
            'compare_price'       => 'Compare Price',
            'stock_quantity'      => 'Stock Quantity',
            'low_stock_threshold' => 'Low Stock Threshold',
            'weight'              => 'Weight',
            'is_active'           => 'Active Status',
            'images'              => 'Product Images',
            'images.*'            => 'Product Image',
        ];
    }
}
