<?php

namespace Condoedge\Crm\Facades;

use Kompo\Auth\Facades\FacadeEnum;

/**
 * @mixin \Condoedge\Crm\Kompo\Inscriptions\InscriptionTypeEnum
 */
class InscriptionTypeEnumGlobal extends FacadeEnum
{
    protected static function getFacadeAccessor()
    {
        return 'inscription-type-enum';
    }
}