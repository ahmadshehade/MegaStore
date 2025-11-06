<?php

namespace Modules\ProductManagment\Http\Requests\Api\V1\Product;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\ProductManagment\Models\Product;

class ProductUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $product=$this->route("product");
        return Gate::allows('update', $product);
    }

    public function rules(): array
    {
        $product=$this->route('product');
        return [
            'name'        => ['sometimes', 'string', 'min:3', 'max:150',Rule::unique('products')->ignore($product->id)],
            'description' => ['sometimes', 'string', 'min:10', 'max:255'],
            'price'       => ['sometimes', 'numeric', 'min:0.01', 'max:99999.99'],
            'stock'       => ['sometimes', 'integer', 'min:0', 'max:999'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'seller_id'   => [ 'integer', 'exists:users,id'],
            'images'      => ['sometimes', 'array'],
            'images.*'    => ['file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            // name
            'name.min'      => 'The :attribute must be at least 3 characters.',
            'name.max'      => 'The :attribute may not exceed 150 characters.',
            

            // description
            'description.min' => 'The :attribute must be at least 10 characters.',
            'description.max' => 'The :attribute may not exceed 255 characters.',

            // price
            
            'price.numeric'  => 'The :attribute must be a valid number.',
            'price.min'      => 'The :attribute must be greater than 0.',
            'price.max'      => 'The :attribute may not exceed 99999.99.',

            // stock
            'stock.integer' => 'The :attribute must be an integer.',
            'stock.min'     => 'The :attribute cannot be negative.',
            'stock.max'     => 'The :attribute may not exceed 999.',

            // category
            'category_id.integer' => 'The :attribute field is integer.',
            'category_id.exists'   => 'The selected :attribute does not exist.',

            // seller
            'seller_id.integer' => 'The :attribute field is integer.',
            'seller_id.exists'   => 'The selected :attribute does not exist.',

            // images
            'images.array'     => 'The :attribute must be an array.',
            'images.*.file'    => 'Each file in :attribute must be a valid file.',
            'images.*.mimes'   => 'The :attribute must be a file of type: jpg, jpeg, png, or webp.',
            'images.*.max'     => 'Each image in :attribute may not be larger than 2MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'        => 'product name',
            'description' => 'product description',
            'price'       => 'product price',
            'stock'       => 'stock quantity',
            'category_id' => 'product category',
            'seller_id'   => 'seller',
            'images'      => 'product images',
        ];
    }
}
