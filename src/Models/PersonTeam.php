<?php

namespace Condoedge\Crm\Models;

use App\Models\Crm\Person;
use Kompo\Auth\Models\Model;

class PersonTeam extends Model
{
	use \Kompo\Auth\Models\Teams\BelongsToTeamTrait;
	use \Condoedge\Crm\Models\BelongsToPersonTrait;
	
	/* RELATIONS */

	/* SCOPES */
	public function scopeActive($query)
	{
		return $query->whereNull('to');
	}

	/* CALCULATED FIELDS */

	/* ACTIONS */

	/* ELEMENTS */
}
