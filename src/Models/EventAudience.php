<?php

namespace Condoedge\Crm\Models;

use Condoedge\Utils\Models\ModelBase;

class EventAudience extends ModelBase //No need for softdeletes here
{
	use \Condoedge\Crm\Models\BelongsToEventTrait;

	/* RELATIONS */

	/* SCOPES */
	public function scopeForAudienceConcern($query, $concern)
	{
		$query->where('audience_concern', $concern);
	}

	/* CALCULATED FIELDS */

	/* ACTIONS */

	/* ELEMENTS */
}
