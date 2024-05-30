<?php

use Condoedge\Crm\Models\GenderEnum;

\Kompo\Elements\Element::macro('asGenderPill', function (GenderEnum $gender) {
    return $this->asPill()
        ->class($gender->bgColor() . ' bg-opacity-20 ' . $gender->textColor());
});