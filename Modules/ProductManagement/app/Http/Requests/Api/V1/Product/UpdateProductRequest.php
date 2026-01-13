<?php

namespace Modules\ProductManagement\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\ProductManagement\Models\Product;

class UpdateProductRequest extends FormRequest
{
    /**
     * Prepare data for validation.
     *
     * - Only generate slug when name exists AND slug not provided.
     */
    public function prepareForValidation(): void
    {
        $name = $this->input('name');

        if ($name && !$this->filled('slug')) {
            $this->merge([
                'slug' => Str::slug($name) . '_' . Str::random(12),
            ]);
        }
    }

    /**
     * Authorization.
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('product'));
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        $routeProduct = $this->route('product');
        $productId = $routeProduct instanceof Product ? $routeProduct->id : (is_numeric($routeProduct) ? (int) $routeProduct : null);

        return [
            'name'              => ['sometimes', 'string', 'max:255'],
            'slug'              => array_filter([
                'nullable',
                'string',
                'max:255',
                $productId ? Rule::unique('products', 'slug')->ignore($productId) : Rule::unique('products', 'slug'),
            ]),
            'sku'               => array_filter([
                'nullable',
                'string',
                'max:255',
                $productId ? Rule::unique('products', 'sku')->ignore($productId) : Rule::unique('products', 'sku'),
            ]),
            'category_id'       => ['sometimes', 'integer', 'exists:categories,id'],
            'short_description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description'       => ['sometimes', 'nullable', 'string'],
            'brand'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'status'            => ['sometimes', Rule::in(['draft', 'active', 'archived'])],
            'is_featured'       => ['sometimes', 'boolean'],
            'meta'              => ['sometimes', 'nullable', 'array'],
            'images'            => ['sometimes', 'array'],
            'images.*'          => ['nullable', 'file', 'image', 'max:5120'],
        ];
    }

    /**
     * Professional and user-friendly validation messages.
     */
    public function messages(): array
    {
        return [
            'name.string'              => 'The product name must be a valid text.',
            'name.max'                 => 'The product name must not exceed :max characters.',
            'slug.string'              => 'The product slug must be a valid text.',
            'slug.max'                 => 'The product slug must not exceed :max characters.',
            'slug.unique'              => 'The chosen product URL (slug) is already taken.',
            'sku.unique'               => 'The SKU is already in use.',
            'category_id.integer'      => 'The category identifier must be a valid number.',
            'category_id.exists'       => 'The selected category was not found.',
            'short_description.string' => 'The short description must be a valid text.',
            'short_description.max'    => 'The short description must not exceed :max characters.',
            'description.string'       => 'The product description must be a valid text.',
            'brand.string'             => 'Brand must be a valid text.',
            'brand.max'                => 'Brand must not exceed :max characters.',
            'status.in'                => 'Invalid status. Allowed values are: draft, active, archived.',
            'is_featured.boolean'      => 'The featured flag must be true or false.',
            'meta.array'               => 'Meta must be a valid array.',
            'images.array'             => 'Images must be provided as an array.',
            'images.*.file'            => 'Each image must be a valid uploaded file.',
            'images.*.image'           => 'Each uploaded file must be an image (jpeg, png, bmp, gif, svg, or webp).',
            'images.*.max'             => 'Each image must be smaller than :max kilobytes (5MB).',
        ];
    }

    /**
     * Custom attribute names for clearer error output.
     */
    public function attributes(): array
    {
        return [
            'name'              => 'Product Name',
            'slug'              => 'Product URL (slug)',
            'sku'               => 'SKU',
            'category_id'       => 'Category',
            'short_description' => 'Short Description',
            'description'       => 'Full Description',
            'brand'             => 'Brand',
            'status'            => 'Status',
            'is_featured'       => 'Featured',
            'meta'              => 'Meta Data',
            'images'            => 'Product Images',
            'images.*'          => 'Product Image',
        ];
    }
}
