<?php

use Condoedge\Crm\Kompo\Inscriptions\InscriptionTypeEnum;

return [
    'event-model-namespace' => Condoedge\Crm\Models\Event::class,
    'person-model-namespace' => Condoedge\Crm\Models\Person::class,

    'inscription-model-namespace' => getAppClass(App\Models\Inscriptions\Inscription::class, Condoedge\Crm\Models\Inscription::class),

    'inscription-types' => InscriptionTypeEnum::cases(),
];