<?php

namespace Modules\ProductManagement\Rules;
use Illuminate\Contracts\Validation\Rule;

class Decimal82Rule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return preg_match('/^\d{1,6}(\.\d{1,2})?$/', $value);
    }

    public function message(): string
    {
        return ':attribute must be a valid decimal (max 6 digits before decimal and 2 after).';
    }
}
