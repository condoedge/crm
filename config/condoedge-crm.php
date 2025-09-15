<?php

use Condoedge\Crm\Kompo\Inscriptions\InscriptionTypeEnum;

return [
    'event-model-namespace' => Condoedge\Crm\Models\Event::class,
    'person-model-namespace' => Condoedge\Crm\Models\Person::class,

    'inscription-model-namespace' => getAppClass(\App\Models\Inscriptions\Inscription::class, Condoedge\Crm\Models\Inscription::class),

    'inscription-types' => InscriptionTypeEnum::cases(),

    'inscription-type-enum' =>  getAppClass(\App\Kompo\Inscriptions\InscriptionTypeEnum::class, \Condoedge\Crm\Kompo\Inscriptions\InscriptionTypeEnum::class),

    'person-team-type-enum' => getAppClass(\App\Models\Crm\PersonTeamTypeEnum::class, \Condoedge\Crm\Models\PersonTeamTypeEnum::class),

    'manage-payment-from-inscription' => true,
];
