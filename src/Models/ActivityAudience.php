<?php

namespace Condoedge\Crm\Models;

use Kompo\Auth\Models\ModelBase;

class ActivityAudience extends ModelBase //No need for softdeletes here
{
	use \Condoedge\Crm\Models\BelongsToActivityTrait;

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
