<?php

namespace Modules\ProductManagement\Http\Requests\Api\V1\ProductVariants;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\ProductManagement\Rules\Decimal122Rule;
use Modules\ProductManagement\Rules\Decimal82Rule;

class StoreProductVariantsRequest extends BaseRequest
{
    /**
     * Authorization.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', ProductVariant::class);
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'product_id'          => ['required', 'integer', 'exists:products,id'],

            'sku'                 => [
                'required',
                'string',
                'min:5',
                'max:32',
                Rule::unique('product_variants', 'sku'),
            ],

            'price'               => ['required', new Decimal122Rule],
            'compare_price'       => ['nullable', new Decimal122Rule],

            'stock_quantity'      => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],

            'weight'              => ['nullable', new Decimal82Rule],
            'is_active'           => ['sometimes', 'boolean'],

            'images'              => ['sometimes', 'array'],
            'images.*'            => ['nullable', 'file', 'image', 'max:5120'],
        ];
    }

    /**
     * Custom messages.
     */
    public function messages(): array
    {
        return [
            'product_id.required'          => 'Product id is required.',
            'product_id.exists'            => 'Selected product does not exist.',

            'sku.required'                 => 'SKU is required.',
            'sku.unique'                   => 'This SKU is already in use.',
            'sku.min'                      => 'SKU must be at least :min characters.',
            'sku.max'                      => 'SKU must not exceed :max characters.',

            'price.required'               => 'Price is required.',

            'stock_quantity.required'      => 'Stock quantity is required.',
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
