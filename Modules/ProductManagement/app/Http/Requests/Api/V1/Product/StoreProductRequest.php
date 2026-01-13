<?php

namespace Modules\ProductManagement\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\ProductManagement\Models\Product;

class StoreProductRequest extends FormRequest
{
    /**
     * Prepare data before validation.
     */
    public function prepareForValidation(): void
    {
        $name = $this->input('name', '');
        $slugBase = $name ? Str::slug($name) : 'product';
        $slugRandom = Str::random(12);

        $this->merge([
            'slug'       => $this->input('slug') ?: "{$slugBase}_{$slugRandom}",
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Authorization using Laravel Gate.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Product::class);
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')],
            'sku'               => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')],
            'category_id'       => ['required', 'integer', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'brand'             => ['nullable', 'string', 'max:255'],
            'status'            => ['required', Rule::in(['draft','active','archived'])],
            'is_featured'       => ['sometimes', 'boolean'],
            'meta'              => ['nullable', 'array'],
            'created_by'        => ['required', 'integer', 'exists:users,id'],
            'images'            => ['sometimes', 'array'],
            'images.*'          => ['nullable', 'file', 'image', 'max:5120'],
        ];
    }

    /**
     * Validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required'            => 'Please provide the product name.',
            'name.string'              => 'The product name must be a valid text.',
            'name.max'                 => 'The product name must not exceed :max characters.',
            'slug.string'              => 'The product slug must be a valid text.',
            'slug.max'                 => 'The product slug must not exceed :max characters.',
            'slug.unique'              => 'The chosen product URL (slug) is already taken.',
            'sku.unique'               => 'The SKU is already in use.',
            'category_id.required'     => 'Please choose a category for this product.',
            'category_id.integer'      => 'The category identifier must be a valid number.',
            'category_id.exists'       => 'The selected category was not found.',
            'short_description.string' => 'The short description must be a valid text.',
            'short_description.max'    => 'The short description must not exceed :max characters.',
            'description.string'       => 'The product description must be a valid text.',
            'brand.string'             => 'Brand must be a valid text.',
            'brand.max'                => 'Brand must not exceed :max characters.',
            'status.required'          => 'Please specify the product status.',
            'status.in'                => 'Invalid status. Allowed values are: draft, active, archived.',
            'is_featured.boolean'      => 'The featured flag must be true or false.',
            'meta.array'               => 'Meta must be a valid array.',
            'created_by.required'      => 'Creator identification is required.',
            'created_by.integer'       => 'Creator id must be a valid integer.',
            'created_by.exists'        => 'The specified creator (user) was not found.',
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
            'created_by'        => 'Creator',
            'images'            => 'Product Images',
            'images.*'          => 'Product Image',
        ];
    }
}
