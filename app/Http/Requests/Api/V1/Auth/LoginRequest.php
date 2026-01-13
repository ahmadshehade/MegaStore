<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

   

    /**
     * Validation rules.
     *
     * @return array{firstname: string[], lastname: string[], name: string[], email: string[], password: string[]}
     */
    public function rules(): array
    {
        return [
            'email'     => ['required', 'email', 'exists:users,email'],
            'password'  => [
                'required',
                'string',
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
         
            // Email
            'email.required' => 'Email is required.',
            'email.email'    => 'Please provide a valid email address.',
            'email.exists'=>'Email Must Be In Users Email',
            

            // Password
            'password.required'  => 'Password is required.',
            'password.string'    => 'Password must be a valid string.',
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
