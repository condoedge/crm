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

	protected $fillable = [
		'team_role_id',
	];

	protected $casts = [
		'status' => PersonTeamStatusEnum::class,
		'from' => 'datetime',
        'to' => 'datetime',
	];
	
	/* RELATIONS */
	public function teamRole()
	{
		return $this->belongsTo(TeamRole::class);
	}

	/* SCOPES */
	public function scopeActive($query)
	{
		return $query->where(fn($q) => $q
			->where('to', '>', now())
			->orWhereNull('to')
		);
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

	public static function createFromTeamRole($teamRole, $status = null, $expirationDate = null)
	{
		if ($personTeam = static::where('team_role_id', $teamRole->id)->first()) {
			$personTeam->status = $status ?? $personTeam->status;
			$personTeam->to = $expirationDate;
			$personTeam->save();

			return $personTeam;
		}

		$personTeam = new static;
		$personTeam->status = $status ?? PersonTeamStatusEnum::ACTIVE;
		$personTeam->team_role_id = $teamRole->id;
		$personTeam->person_id = PersonModel::where('user_id', $teamRole->user_id)->first()->id;
		$personTeam->team_id = $teamRole->team_id;
		$personTeam->from = now();
		$personTeam->to = $expirationDate;
		$personTeam->save();

		return $personTeam;
	}

	public static function getOrCreateForInscription($inscription, $teamRole)
	{
		$personTeam = static::where('person_id', $inscription->person->getRegisteringPerson()->id)->where('team_id', $inscription->team_id)->whereNull('team_role_id')->first();

		if (!$personTeam) {
			$personTeam =  static::createFromTeamRole($teamRole);
		} else {
			$personTeam->team_role_id = $teamRole->id;
		}

		$personTeam->status = $inscription->hasPendingPayment() ? PersonTeamStatusEnum::PENDING_PAYMENT : PersonTeamStatusEnum::ACTIVE;
		$personTeam->to = $inscription->getExpirationDate();
		$personTeam->inscription_type = $inscription->type?->value;
		$personTeam->save();

		return $personTeam;
	}

	/* ELEMENTS */
	public function getStatusPillElement()
	{
		return _Pill($this->status->label())->class($this->status->color());
	}
}
