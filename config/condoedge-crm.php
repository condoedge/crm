<?php

use Condoedge\Crm\Kompo\Inscriptions\InscriptionTypeEnum;

return [
    'event-model-namespace' => Condoedge\Crm\Models\Event::class,
    'person-model-namespace' => Condoedge\Crm\Models\Person::class,

    'inscription-types' => InscriptionTypeEnum::cases(),
];