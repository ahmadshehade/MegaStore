<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Merge firstname + lastname into 'name' (lowercased).
     */
    protected function prepareForValidation(): void
    {
        $first = (string) $this->input('firstname', '');
        $last  = (string) $this->input('lastname', '');

        $this->merge([
            'name' => strtolower(trim($first . ' ' . $last)),
        ]);
    }

    /**
     * Validation rules.
     *
     * @return array{firstname: string[], lastname: string[], name: string[], email: string[], password: string[]}
     */
    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'max:16', 'min:5'],
            'lastname'  => ['required', 'string', 'max:16', 'min:5'],


            'name'      => ['sometimes', 'string', 'max:32', 'min:10'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'max:32',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/'
            ],
        ];
    }

    /**
     * Custom messages (English).
     */
    public function messages(): array
    {
        return [
            // Firstname
            'firstname.required' => 'First name is required.',
            'firstname.string'   => 'First name must be a valid string.',
            'firstname.max'      => 'First name must not exceed 16 characters.',
            'firstname.min'      => 'First name must be at least 5 characters long.',

            // Lastname
            'lastname.required' => 'Last name is required.',
            'lastname.string'   => 'Last name must be a valid string.',
            'lastname.max'      => 'Last name must not exceed 16 characters.',
            'lastname.min'      => 'Last name must be at least 5 characters long.',

            // Name (generated)
            'name.string' => 'Full name must be a valid string.',
            'name.max'    => 'Full name must not exceed 32 characters.',
            'name.min'    => 'Full name must be at least 10 characters long.',

            // Email
            'email.required' => 'Email is required.',
            'email.email'    => 'Please provide a valid email address.',
            'email.unique'   => 'This email is already registered.',

            // Password
            'password.required'  => 'Password is required.',
            'password.string'    => 'Password must be a valid string.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min'       => 'Password must be at least 8 characters long.',
            'password.max'       => 'Password must not exceed 32 characters.',
            'password.regex'     => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ];
    }

    /**
     * Attribute names for error messages.
     *
     * @return array<string,string>
     */
    public function attributes(): array
    {
        return [
            'firstname' => 'First Name',
            'lastname'  => 'Last Name',
            'name'      => 'Full Name',
            'email'     => 'Email',
            'password'  => 'Password',
        ];
    }
}
