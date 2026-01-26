<?php

namespace Modules\ProductManagement\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

class Decimal122Rule implements Rule
{

    /**
     * Summary of passes
     * @param mixed $attribute
     * @param mixed $value
     * @return bool|int
     */
    public function passes($attribute, $value): bool
    {
        return preg_match('/^\d{1,10}(\.\d{1,2})?$/', $value);
    }

    /**
     * Summary of message
     * @return string
     */
    public function message(): string
    {
        return ':attribute must be a valid decimal (max 10 digits before decimal and 2 after).';
    }
}
