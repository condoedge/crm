<?php

namespace Condoedge\Crm\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;

class MinAgeRule implements ValidationRule
{
    protected $minAge;

    public function __construct($minAge)
    {
        $this->minAge = $minAge;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (Carbon::parse($value)->age < $this->minAge) {
            $fail('translate.error.must-be-at-least-' . $this->minAge . '-years-old');
        }
    }
}