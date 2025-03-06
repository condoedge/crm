<?php

namespace Condoedge\Crm\Facades;

use Kompo\Komponents\Form\KompoModelFacade;

/**
 * @mixin \Condoedge\Crm\Models\Event
 */
class EventModel extends KompoModelFacade
{
    protected static function getModelBindKey()
    {
        return 'event-model';
    }
}