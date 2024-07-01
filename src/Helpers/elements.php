<?php

use Condoedge\Crm\Models\GenderEnum;

\Kompo\Elements\Element::macro('asGenderPill', function (GenderEnum $gender) {
    return $this->asPill()
        ->class($gender->bgColor() . ' '. $gender->textColor());
});

\Kompo\Elements\Element::macro('asGenderPillDesign2', function (GenderEnum $gender) {
    return $this->asPill()
        ->class($gender->bgColor2() . ' '. $gender->textColor2());
});