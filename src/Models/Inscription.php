<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Kompo\Inscriptions\InscriptionTypeEnum;
use Kompo\Auth\Facades\RoleModel;
use Kompo\Auth\Models\Model;
use Kompo\Auth\Models\Teams\BelongsToTeamTrait;

class Inscription extends Model
{
    use BelongsToPersonTrait;
    use BelongsToTeamTrait;

	use \Condoedge\Crm\Models\HasQrCodeTrait;
	public const QRCODE_LENGTH = 8;
	public const QRCODE_COLUMN_NAME = 'qr_inscription'; //To link same inscription members together


    protected $casts = [
        'status' => InscriptionStatusEnum::class,
        'type' => InscriptionTypeEnum::class,
    ];

	/* RELATIONS */
    public function role()
    {
        return $this->belongsTo(RoleModel::getClass());
    }

    public function inscribedBy()
    {
        return $this->belongsTo(PersonModel::getClass(), 'inscribed_by');
    }

	/* SCOPES */

	/* CALCULATED FIELDS */
	public function getInscriptionConfirmationRoute($personId, $eventId)
    {
        return \URL::signedRoute('inscription.confirmation', [
            'inscription_id' => $this->id,
            'event_id' => $eventId,
            'id' => $personId,
        ]);
    }

	public function getPerformRegistrationUrl()
	{
		return \URL::signedRoute('person-registrable.register', [
            'inscription_id' => $this->id,
        ]);
	}

    public function getAcceptInscriptionUrl()
	{
		return \URL::signedRoute('person-registrable.accept', [
            'id' => $this->id,
        ]);
	}

    public static function defaultTypeForPersonEvent()
    {
        return InscriptionTypeEnum::GENERIC;
    }

    public function isApproved()
    {
        $this->status == InscriptionStatusEnum::APPROVED;
    }

	/* ACTIONS */
	public function deleteInscriptionEventsIfAny($personId)
	{
		PersonEvent::where('inscription_id', $this->id)->where('person_id', $personId)->delete();
	}

    public static function getForPerson($personId, $teamId, $inscriptionType)
    {
        return static::where('person_id', $personId)->where('team_id', $teamId)->where('type', $inscriptionType)->first();
    }

    public static function getOrCreateForPerson($personId, $teamId, $inscriptionType, $roleId = null)
    {
        if ($inscription = static::getForPerson($personId, $teamId, $inscriptionType)) {
            return $inscription;
        }

        $inscription = new static;
        $inscription->person_id = $personId;
        $inscription->team_id = $teamId;
        $inscription->type = $inscriptionType;
        $inscription->inscribed_by = auth()->user()?->getRelatedMainPerson()?->id;
        $inscription->role_id = $roleId;
        $inscription->save();

        return $inscription;
    }

    public function confirmUserRegistration($user)
    {
        $person = $this->person->getRegisteringPerson();

        $role = RoleModel::getOrCreate($this->type->getRole($this));

        $teamRole = $user->createTeamRole($this->team, $role->id);

        $person->personTeams()->where('team_id', $this->team->id)->update(['team_role_id' => $teamRole->id]);
        $person->user_id = $user->id;
        $person->save();

        fireRegisteredEvent($user);
    }

	/* ELEMENTS */
    public function statusPill()
    {
        return _Pill($this->status->label())->class($this->status->color());
    }
}
