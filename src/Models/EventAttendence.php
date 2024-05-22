<?php

namespace Condoedge\Crm\Models;

use Kompo\Auth\Models\Model;

class EventAttendence extends Model
{
	/* RELATIONS */
	public function event()
	{
		return $this->belongsTo(Event::class);
	}

	/* SCOPES */

	/* CALCULATED FIELDS */

	/* ACTIONS */

	/* ELEMENTS */
}
