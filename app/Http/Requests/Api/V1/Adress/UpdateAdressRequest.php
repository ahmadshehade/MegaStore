<?php

namespace App\Http\Requests\Api\V1\Adress;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use App\Models\Address;

class UpdateAdressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('address'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type'        => ['sometimes', Rule::in(['shipping', 'billing'])],
            'country'     => ['sometimes', 'string', 'max:100'],
            'state'       => ['nullable', 'string', 'max:100'],
            'city'        => ['sometimes', 'string', 'max:100'],
            'address'     => ['sometimes', 'string', 'max:1000'],
            'postal_code' => ['nullable', 'string', 'max:60'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'is_default'  => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in'           => 'The address type must be either shipping or billing.',
            'country.max'       => 'The country field may not be greater than 100 characters.',
            'city.max'          => 'The city field may not be greater than 100 characters.',
            'address.max'       => 'The address field may not be greater than 1000 characters.',
            'postal_code.max'   => 'The postal code field may not be greater than 60 characters.',
            'phone.max'         => 'The phone field may not be greater than 30 characters.',
            'is_default.boolean'=> 'The default flag must be true or false.',
        ];
    }

    public function attributes(): array
    {
        return [
            'type'        => 'address type',
            'country'     => 'country',
            'state'       => 'state',
            'city'        => 'city',
            'address'     => 'full address',
            'postal_code' => 'postal code',
            'phone'       => 'phone number',
            'is_default'  => 'default flag',
        ];
    }
}
