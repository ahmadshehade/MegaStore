<?php

namespace Modules\ProductManagment\Http\Requests\Api\V1\Category;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Modules\ProductManagment\Models\Category;

class CategoryStoreRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:6', 'max:100','unique:categories,name'],
            'description' => ['sometimes', 'string', 'min:10', 'max:200'],
            'parent_id' => ['sometimes', 'integer', 'in:categories,id'],

        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create', Category::class);
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The :attribute field is required.',
            'name.string' => 'The :attribute must be a valid string.',
            'name.min' => 'The :attribute must be at least :min characters.',
            'name.max' => 'The :attribute may not exceed :max characters.',

            'description.string' => 'The :attribute must be a valid string.',
            'description.min' => 'The :attribute must be at least :min characters.',
            'description.max' => 'The :attribute may not exceed :max characters.',

            'parent_id.integer' => 'The :attribute must be a valid integer.',
            'parent_id.exists' => 'The selected :attribute does not exist.',
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
        ];
    }
}
