<?php

namespace Modules\OrderManagement\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDiscountValue implements Rule
{
    /**
     * Run the validation rule.
     */
      protected $type;

    /**
     * Create a new rule instance.
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value): bool
    {
        if ($this->type === 'percentage') {
            return $value >= 0 && $value <= 100;
        }

        // Fixed amount can be larger
        if ($this->type === 'fixed') {
            return $value >= 0 && $value <= 99999.99;
        }

        return false;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        if ($this->type === 'percentage') {
            return 'Percentage discount must be between 0 and 100.';
        }

        if ($this->type === 'fixed') {
            return 'Fixed discount must be between 0 and 99999.99.';
        }

        return 'Invalid discount value.';
    }
}
