<?php

namespace Condoedge\Crm\Facades;

use Kompo\Komponents\Form\KompoModelFacade;

/**
 * @mixin \Condoedge\Crm\Models\Person
 */
class PersonModel extends KompoModelFacade
{
    protected static function getModelBindKey()
    {
        return 'person-model';
    }
}