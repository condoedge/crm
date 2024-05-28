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

	/* CALCULATED FIELDS */

	/* ACTIONS */
	public static function createFirstJoin($personId, $teamId)
	{
		$pt = new PersonTeam();
		$pt->person_id = $personId;
		$pt->team_id = $teamId;
		$pt->from = date('Y-m-d');
		$pt->save();
	}

	/* ELEMENTS */
}
