<?php

namespace Condoedge\Crm\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;

class MinAgeBySchoolYear implements ValidationRule
{
    protected $minAge;
    protected $month;
    protected $day;

    public function __construct($minAge, $month, $day)
    {
        $this->minAge = $minAge;
        $this->month = $month;
        $this->day = $day;
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
        $dob = Carbon::parse($value);
        $age = $dob->age;

        if ($dob->month > $this->month || ($dob->month === $this->month && $dob->day > $this->day)) {
            $age--;
        }

        if ($age < $this->minAge) {
            $fail(__('error.must-be-at-least') . ' ' . $this->minAge . ' ' . __('error.years-old'));
        }
    }
}