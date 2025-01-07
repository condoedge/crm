<?php

namespace Condoedge\Crm\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneNumberRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $ph = new PhoneNumber($value, ['CA', 'US', 'INTERNATIONAL']);
            $ph = $ph->formatInternational();
        } catch (\Throwable $e) {
            $fail(__('error.must-be-a-valid-phone-number'));
        }
    }
}
