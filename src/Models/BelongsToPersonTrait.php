<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\PersonModel;

trait BelongsToPersonTrait
{
    /* RELATIONS */
    public function person()
    {
        return $this->belongsTo(PersonModel::getClass());
    }

    /* SCOPES */
    public function scopeForPerson($query, $idOrIds)
    {
        scopeWhereBelongsTo($query, 'person_id', $idOrIds);
    }

    /* CALCULATED FIELDS */
    public function getPersonLabelAttribute()
    {
        return $this->person?->full_name;
    }

    /* ACTIONS */

    /* ELEMENTS */
}
