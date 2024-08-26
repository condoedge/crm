<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\PersonModel;
use Kompo\Auth\Models\Model;
use Kompo\Auth\Models\Teams\TeamRole;
use Kompo\Auth\Models\Teams\TeamRoleStatusEnum;

class PersonTeam extends Model
{
	use \Kompo\Auth\Models\Teams\BelongsToTeamTrait;
	use \Condoedge\Crm\Models\BelongsToPersonTrait;
	
	/* RELATIONS */
	public function teamRole()
	{
		return $this->belongsTo(TeamRole::class);
	}

	/* SCOPES */
	public function scopeActive($query)
	{
		return $query->whereNull('to');
	}

	/* CALCULATED FIELDS */
	public function getStatusAttribute()
	{
		return $this->to?->isPast()? TeamRoleStatusEnum::FINISHED : TeamRoleStatusEnum::IN_PROGRESS;
	}

	/* ACTIONS */
	public function terminate()
	{
		$this->to = now();
		$this->save();

		$this->teamRole?->terminate();
	}

	public function deleteAsignation()
	{
		$this->teamRole?->deleteAsignation();
		$this->delete();
	}

	public static function createFromTeamRole($teamRole)
	{
		$personTeam = new static;
		$personTeam->team_role_id = $teamRole->id;
		$personTeam->person_id = PersonModel::whereHas('relatedUser', fn($q) => $q->where('id', $teamRole->user_id))->first()->id;
		$personTeam->team_id = $teamRole->team_id;
		$personTeam->from = now();
		$personTeam->save();
	}

	/* ELEMENTS */
	public function getStatusPillElement()
	{
		return _Pill($this->status->label())->class($this->status->color());
	}
}
