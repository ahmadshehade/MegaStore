<?php

namespace Modules\ProductManagment\Http\Requests\Api\V1\Category;

use App\Http\Requests\BaseRequest;
use App\Models\BaseModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Modules\ProductManagment\Models\Category;

class CategoryUpdateRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => ['sometimes', 'string', 'min:6', 'max:100', Rule::unique('categories')->ignore($category?->id)],
            'description' => ['sometimes', 'string', 'min:10', 'max:200'],
            'parent_id' => ['sometimes', 'integer', 'in:categories,id'],

            'images' => ['sometimes', 'array'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],


        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('update', Category::class);
    }


    public function messages(): array
    {
        return [
            'name.unique' => 'The :attribute field is unique.',
            'name.string' => 'The :attribute must be a valid string.',
            'name.min' => 'The :attribute must be at least :min characters.',
            'name.max' => 'The :attribute may not exceed :max characters.',

            'description.string' => 'The :attribute must be a valid string.',
            'description.min' => 'The :attribute must be at least :min characters.',
            'description.max' => 'The :attribute may not exceed :max characters.',

            'parent_id.integer' => 'The :attribute must be a valid integer.',
            'parent_id.exists' => 'The selected :attribute does not exist.',

            'images.array' => 'The :attribute field must be a valid array.',
            'images.*.file' => 'Each :attribute must be a valid file.',
            'images.*.image' => 'Each :attribute must be a valid image file.',
            'images.*.mimes' => 'Each :attribute must be of type: JPG, JPEG, PNG, GIF, or WEBP.',
            'images.*.max' => 'Each :attribute must not exceed 5MB in size.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'category name',
            'description' => 'category description',
            'parent_id' => 'parent category',
            'images' => 'images collection',
            'images.*' => 'uploaded image file',
        ];
    }
}


