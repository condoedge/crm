<?php

namespace Condoedge\Crm\Models;

trait BelongsToPersonTrait
{
	/* RELATIONS */
    public function person()
    {
        return $this->belongsTo(config('condoedge-crm.person-model-namespace'));
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
