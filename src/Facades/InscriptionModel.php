<?php

namespace Condoedge\Crm\Facades;

use Kompo\Komponents\Form\KompoModelFacade;

/**
 * @mixin \Condoedge\Crm\Models\Inscription
 */
class InscriptionModel extends KompoModelFacade
{
    protected static function getModelBindKey()
    {
        return 'inscription-model';
    }
}
