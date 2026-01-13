<?php

namespace Modules\ProductManagement\Http\Requests\Api\V1\Catgories;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\ProductManagement\Models\Category;

class UpdateCategoryRequest extends BaseRequest
{
    /**
     * Prepare data before validation.
     */
    public function prepareForValidation(): void
    {
        if ($this->filled('name')) {
            $this->merge([
                'slug' => Str::slug($this->input('name')) . '_' . Str::random(5),
            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // route('category') may be a Category model or an id depending on your route binding
        return Gate::allows('update', $this->route('category'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $routeCategory = $this->route('category');
        $categoryId = $routeCategory instanceof Category ? $routeCategory->id : $routeCategory;

        return [
            'name'         => [
                'sometimes',
                'string',
                'min:2',
                'max:32',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'descriptions' => ['nullable', 'string', 'min:5', 'max:50'],
            'slug'         => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'parent_id'    => ['nullable', 'integer', 'exists:categories,id'],
            'images' => ['sometimes', 'array'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.string'     => 'The category name must be a string.',
            'name.unique'     => 'The category name is already taken.',
            'name.min'        => 'The category name must contain at least 2 characters.',
            'name.max'        => 'The category name may not be greater than 32 characters.',

            'descriptions.string' => 'The description must be a string.',
            'descriptions.min'    => 'The description must contain at least 5 characters.',
            'descriptions.max'    => 'The description may not be greater than 50 characters.',

            'slug.string'     => 'The slug must be a string.',
            'slug.unique'     => 'The slug is already in use.',
            'slug.max'        => 'The slug may not be greater than 255 characters.',

            'parent_id.integer' => 'The parent category ID must be an integer.',
            'parent_id.exists'  => 'The selected parent category does not exist.',

            'images.array' => 'The :attribute field must be a valid array.',
            'images.*.file' => 'Each :attribute must be a valid file.',
            'images.*.image' => 'Each :attribute must be a valid image file.',
            'images.*.mimes' => 'Each :attribute must be of type: JPG, JPEG, PNG, GIF, or WEBP.',
            'images.*.max' => 'Each :attribute must not exceed 5MB in size.',
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'name'         => 'category name',
            'descriptions' => 'category description',
            'slug'         => 'slug',
            'parent_id'    => 'parent category',
            'images' => 'images collection',
            'images.*' => 'uploaded image file',
        ];
    }
}
