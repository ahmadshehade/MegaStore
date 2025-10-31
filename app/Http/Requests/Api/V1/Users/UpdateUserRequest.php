<?php

namespace App\Http\Requests\Api\V1\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('admin-job', $this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:32', 'min:10'],
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($this->user()->id)],
            'password' => [
                'sometimes',
                'string',
                'confirmed',
                'min:8',
                'max:32',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/'
            ],
        ];
    }

    /**
     * Custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'The :attribute must be a valid text.',
            'name.min' => 'The :attribute must be at least :min characters long.',
            'name.max' => 'The :attribute may not exceed :max characters.',

            'email.email' => 'Please provide a valid :attribute address.',
            'email.unique' => 'This :attribute is already taken. Please choose another one.',

            'password.required' => 'The :attribute field is required.',
            'password.confirmed' => 'The :attribute confirmation does not match.',
            'password.min' => 'The :attribute must be at least :min characters long.',
            'password.max' => 'The :attribute may not exceed :max characters.',
            'password.regex' => 'The :attribute must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.',
        ];
    }

    /**
     * Custom attribute names for clarity in error messages.
     */
    public function attributes(): array
    {
        return [
            'name' => 'user name',
            'email' => 'email address',
            'password' => 'password',
        ];
    }
}
