<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

class Decimal102Rule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/^\d{1,10}(\.\d{1,2})?$/', (string) $value) === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid number with up to 10 digits before the decimal point and up to 2 digits after.';
    }
}
