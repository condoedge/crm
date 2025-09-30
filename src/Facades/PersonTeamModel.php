<?php

namespace Condoedge\Crm\Facades;

use Kompo\Komponents\Form\KompoModelFacade;

/**
 * @mixin \Condoedge\Crm\Models\PersonTeam
 */
class PersonTeamModel extends KompoModelFacade
{
    protected static function getModelBindKey()
    {
        return 'person-team-model';
    }
}
