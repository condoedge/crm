<?php

namespace Condoedge\Crm\Models;

use Kompo\Auth\Models\ModelBase;

class EventAudience extends ModelBase //No need for softdeletes here
{
	/* RELATIONS */
	public function event()
	{
		return $this->belongsTo(Event::class);
	}

	/* SCOPES */
	public function scopeForAudienceConcern($query, $concern)
	{
		$query->where('audience_concern', $concern);
	}

	/* CALCULATED FIELDS */

	/* ACTIONS */

	/* ELEMENTS */
}
