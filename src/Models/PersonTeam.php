<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Facades\PersonTeamTypeEnumGlobal;
use Condoedge\Utils\Models\Model;
use Kompo\Auth\Models\Teams\TeamRole;

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
        'role_type' => PersonTeamTypeEnum::class,
    ];

    /* RELATIONS */
    public function teamRole()
    {
        return $this->belongsTo(TeamRole::class);
    }

    public function teamRoleIncludingDeleted()
    {
        return $this->teamRole()->withTrashed()->withoutGlobalScopes();
    }

    /* SCOPES */
    public function scopeActive($query)
    {
        return $query->whereNull('person_teams.deleted_at'); // Removed, now just using deleted_at to determine active status.
    }

    /* CALCULATED FIELDS */
    // public function getStatusAttribute()
    // {
    // 	return $this->to?->isPast()? TeamRoleStatusEnum::FINISHED : TeamRoleStatusEnum::IN_PROGRESS;
    // }

    public function getRoleName()
    {
        return $this->teamRoleIncludingDeleted?->roleRelation?->name ?: $this->occupation ?: __('crm.unknown');
    }

    /* ACTIONS */
    public function terminate()
    {
        $this->to = now();
        $this->deleted_at = now();
        $this->status = PersonTeamStatusEnum::TERMINATED;
        $this->save();

        $this->teamRole?->terminate();
    }

    public function moveToAnotherUnit($teamId)
    {
        $this->team_id = $teamId;
        $this->save();

        $teamRole = $this->teamRole;
        $teamRole->team_id = $teamId;
        $teamRole->save();
    }

    public static function createFromTeamRole($teamRole, $status = null, $expirationDate = null, $inscription = null, $personTeamType = null)
    {
        if ($personTeam = static::where('team_role_id', $teamRole->id)->first()) {
            $personTeam->status = $status ?? $personTeam->status;
            $personTeam->to = $expirationDate;
            $personTeam->role_type = $personTeamType ?? $personTeam->role_type ?? PersonTeamTypeEnumGlobal::getByRole($teamRole->id);
            $personTeam->last_inscription_id = $inscription?->id ?? $personTeam->last_inscription_id;
            $personTeam->inscription_type = $inscription?->type?->value ?? $personTeam->inscription_type;
            $personTeam->save();

            return $personTeam;
        }

        $personTeam = new static();
        $personTeam->status = $status ?? PersonTeamStatusEnum::ACTIVE;
        $personTeam->team_role_id = $teamRole->id;
        $personTeam->person_id = PersonModel::where('user_id', $teamRole->user_id)->first()->id;
        $personTeam->team_id = $teamRole->team_id;
        $personTeam->from = now();
        $personTeam->to = $expirationDate;
        $personTeam->role_type = $personTeamType ?? PersonTeamTypeEnumGlobal::getByRole($teamRole->id);
        $personTeam->inscription_type = $inscription?->type?->value;
        $personTeam->last_inscription_id = $inscription?->id;
        $personTeam->save();

        return $personTeam;
    }

    // TODO
    public static function getOrCreateForAdultInscription($inscription, $teamRole)
    {
        $personTeam = static::where('person_id', $inscription->person->getRegisteringPerson()->id)->where('team_id', $inscription->team_id)
            ->where(
                fn ($q) => $q->whereNull('team_role_id')
                ->orWhere('team_role_id', $teamRole->id)
            )->first();

        if (!$personTeam) {
            $personTeam =  static::createFromTeamRole($teamRole, expirationDate: $inscription->getExpirationDate(), inscription: $inscription);
        } else {
            $personTeam->team_role_id = $teamRole->id;
        }

        $personTeam->role_type = $inscription->type->getAdultPersonTeamType();
        $personTeam->status = $inscription->type->getSpecificPersonTeamStatus($inscription);
        $personTeam->to = $inscription->getExpirationDate();
        $personTeam->inscription_type = $inscription->type?->value;
        $personTeam->last_inscription_id = $inscription->id;
        $personTeam->save();

        return $personTeam;
    }

    public function markAsPaid()
    {
        $this->status = PersonTeamStatusEnum::ACTIVE;
        $this->save();
    }

    // We're using HasSecurity plugin that handles deleting event to manage security restrictions.
    public function deletable()
    {
        return true;
    }

    public function delete()
    {
        $this->teamRole?->delete();

        return parent::delete();
    }

    /* ELEMENTS */
    public function getStatusPillElement()
    {
        return _Pill($this->status->label())->class($this->status->color())->class('text-white');
    }
}
