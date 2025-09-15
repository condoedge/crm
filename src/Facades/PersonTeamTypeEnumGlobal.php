<?php

namespace Condoedge\Crm\Facades;

use Condoedge\Utils\Facades\FacadeEnum;

/**
 * @mixin \Condoedge\Crm\Models\PersonTeamTypeEnum
 */
class PersonTeamTypeEnumGlobal extends FacadeEnum
{
    protected static function getFacadeAccessor()
    {
        return 'person-team-type-enum';
    }
}
