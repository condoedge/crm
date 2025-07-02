<?php

use Condoedge\Crm\Models\GenderEnum;

\Kompo\Elements\Element::macro('asGenderPill', function (?GenderEnum $gender) {
    return $this->asPill()
        ->when($gender, fn ($e) => $e->class($gender->bgColor() . ' '. $gender->textColor()));
});

\Kompo\Elements\Element::macro('asGenderPillDesign2', function (?GenderEnum $gender) {
    return $this->asPill()
        ->when($gender, fn ($e) => $e->class($gender->bgColor2() . ' '. $gender->textColor2()));
});

function _PhoneInput($label = 'crm.phone')
{
    return _ValidatedInput($label)
        ->type('tel')
        ->formatModels([
            '^(\d{3})(\d{3})(.*)' => '$1-$2-$3',
        ])
        ->allow('^\d{0,10}$')
        ->validate('^\d{3}(?:[-\s]?\d{3})(?:[-\s]?\d{4})$');
}
