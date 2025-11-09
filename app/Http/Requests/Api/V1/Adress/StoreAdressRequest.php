<?php

namespace App\Http\Requests\Api\V1\Adress;

use App\Http\Requests\BaseRequest;
use App\Models\Address;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreAdressRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Use the Address policy create ability
        return Gate::allows('create', Address::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'user_id' => [ 'exists:users,id'],


            'type' => ['sometimes', 'required', Rule::in(['shipping', 'billing'])],


            'country' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:1000'],


            'postal_code' => ['nullable', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:30'],

          
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Custom attribute names for validation messages.
     *
     * @return array<string,string>
     */
    public function attributes(): array
    {
        return [
            'user_id'     => 'user',
            'type'        => 'address type',
            'country'     => 'country',
            'state'       => 'state',
            'city'        => 'city',
            'address'     => 'address',
            'postal_code' => 'postal code',
            'phone'       => 'phone',
            'is_default'  => 'default flag',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string,string|array>
     */
    public function messages(): array
    {
        return [
            'required'            => 'The :attribute field is required.',
            'exists'              => 'The selected :attribute is invalid.',
            'string'              => 'The :attribute must be a string.',
            'max'                 => 'The :attribute may not be greater than :max characters.',
            'boolean'             => 'The :attribute field must be true or false.',
            'type.in'             => 'The selected :attribute is invalid. Allowed: shipping, billing.',
            'user_id.exists'      => 'The specified user does not exist.',
        ];
    }
}
