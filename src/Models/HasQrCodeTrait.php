<?php

namespace Condoedge\Crm\Models;

trait HasQrCodeTrait
{
    /* RELATIONS */
    public function event()
    {
        return $this->belongsTo(config('condoedge-calendar.event-model-namespace'));
    }

    /* CALCULATED FIELDS */
    public function getActivityName()
    {
        return $this->event->name_av;
    }

    /* ACTIONS */

    /* SCOPES */
    public function scopeForEvent($query, $idOrIds)
    {
        scopeWhereBelongsTo($query, 'event_id', $idOrIds);
    }

}
