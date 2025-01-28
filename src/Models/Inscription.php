<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\EventModel;
use Condoedge\Crm\Facades\InscriptionModel;
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

    public function relatedInscriptions()
    {
        return $this->hasMany(InscriptionModel::getClass(), 'related_inscription_id');
    }

    public function parentInscription()
    {
        $this->belongsTo(InscriptionModel::getClass(), 'related_inscription_id');
    }

    public function event()
    {
        return $this->belongsTo(EventModel::getClass());
    }

	/* SCOPES */
    public function scopeAwaitingApproval($query)
    {
        return $query->where('status', InscriptionStatusEnum::FILLED);
    }

    public function scopeCountsInTotal($query)
    {
        return $query->whereIn('status', [
            InscriptionStatusEnum::COMPLETED_SUCCESSFULLY, 
            InscriptionStatusEnum::FILLED,
            InscriptionStatusEnum::PENDING_PAYMENT
        ]);
    }

	/* CALCULATED FIELDS */
    public function getInscriptionRoute($route, $extra = [])
    {
        return \URL::signedRoute($route, array_merge(
            ['inscription_code' => $this->getQrCodeString()], $extra
        ));
    }

	public function getInscriptionConfirmationRoute($eventId)
    {
        return $this->getInscriptionRoute('inscription.confirmation', ['event_id' => $eventId]);
    }

	public function getPerformRegistrationUrl()
	{
        return $this->getInscriptionRoute('person-registrable.register');
	}

    public function getAcceptInscriptionUrl()
	{
		return \URL::signedRoute('person-registrable.accept', [
            'id' => $this->id,
        ]);
	}

    public function getInscriptionTeamRoute()
    {
        if ($this->event_id) {
            return $this->getInscriptionRoute('inscription.confirmation', [
                'event_id' => $this->event_id,
            ]);
        }

        return $this->getInscriptionRoute('inscription.team');
    }

    public function getInscriptionUnitRoute($teamId)
    {
        return $this->getInscriptionRoute('inscription.unit', [
            'group_id' => $teamId,
        ]);
    }

    public function isApproved()
    {
        return $this->status == InscriptionStatusEnum::APPROVED;
    }

	/* ACTIONS */

    /**
     * @param InscriptionTypeEnum $inscriptionType
     */
    public static function getForMainPerson($personId, $teamId, $inscriptionType, $roleId = null, $justLastYear = true)
    {
        $inscriptionType = is_string($inscriptionType) ? getInscriptionTypes()[$inscriptionType] : $inscriptionType;
        
        return static::where($inscriptionType->basedInInscriptionForOtherPerson() ? 'inscribed_by' : 'person_id', $personId)
            ->when(!$teamId, fn($q) => $q->whereNull('team_id'))
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))->where('type', $inscriptionType->value)
            ->when($roleId, fn($q) => $q->where('role_id', $roleId))
            ->when($justLastYear, fn($q) => $q->whereRaw('YEAR(created_at) = YEAR(CURDATE())'))
            ->first();
    }

    public static function getOrCreateForMainPerson($personId, $teamId, $inscriptionType, $roleId = null, $reregistration = false)
    {
        $inscriptionType = is_string($inscriptionType) ? getInscriptionTypes()[$inscriptionType] : $inscriptionType;

        if ($inscription = static::getForMainPerson($personId, $teamId, $inscriptionType, $roleId)) {
            return $inscription;
        }

        $inscription = new static;
        $inscription->person_id = $inscriptionType->basedInInscriptionForOtherPerson() ? null : $personId;
        $inscription->team_id = $teamId;
        $inscription->type = $inscriptionType->value;
        $inscription->inscribed_by = $inscriptionType->basedInInscriptionForOtherPerson() ? $personId : auth()->user()?->getRelatedMainPerson()?->id;
        $inscription->role_id = $roleId;
        $inscription->is_reregistration = $reregistration;
        $inscription->save();

        return $inscription;
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

        $inscription->getExistentQrOrCreateNew();

        return $inscription;
    }

    public function updatePersonId($personId)
    {
        $this->person_id = $personId;
        $this->save();
    }

    public function getRegistrationUrl()
    {
        if (!$this->type) {
            return route('inscription.landing', [
                'inscription_code' => $this->qr_inscription,
            ]);
        }

        if (!$this->person_id && !$this->inscribed_by) {
            return route('inscription.email.step1', [
                'inscription_code' => $this->qrCode,
                'type' => $this->type
            ]);
        }

        return $this->type->registerRoute($this);
    }

    public function createOrGetRegistrationUrl($personId, $teamId, $type)
    {
        $inscription = static::getOrCreateForMainPerson($personId, $teamId, $type);

        return $inscription->getRegistrationUrl();
    }

    public function getInscriptionDoneRoute()
    {
        return \URL::signedRoute('inscription.done1', [
            'inscription_code' => $this->getQrCodeString(),
        ]);
    }

    public function getFirstRegisteredPerson()
    {
        return $this->getRelatedRegistrations()->first();
    }

    public function getNextRegisteredPerson()
    {
        return $this->getRelatedRegistrations()->where('id', '>', $this->id)->first();
    }

    public function getRelatedRegistrations()
    {
        if (!$this->inscribed_by) return collect();

        return static::where('inscribed_by', $this->inscribed_by)->get();
    }

    public static function getDefaultRegisteredByType()
    {
        //To fill in sisc
    }

    public function acceptInscription()
    {
        $this->status = InscriptionStatusEnum::APPROVED;
        $this->save();
    }

    public function isPayed()
    {
        return true;
    }
    public function validToComplete()
    {
        return (!$this->type->requiresPayment() || $this->isPayed()) && $this->status->accepted();
    }

    public function confirmUserRegistration($user)
    {
        $person = $this->person->getRegisteringPerson();

        $person->user_id = $user->id;
        $person->save();

        $roleId = $this->type->getRole($this);

        if(!$roleId) {
            abort(403, __('error.there-is-not-role-assigned-to-your-inscription'));
        }

        if ($this->inscribed_by || $this->validToComplete()) {
            $role = RoleModel::getOrCreate($this->type->getRole($this));

            $teamRole = $user->createTeamRole($this->team, $role->id);
    
            PersonTeam::getOrCreateForInscription($this, $teamRole);
            $person->user_id = $user->id;
            $person->save();

            $this->status = (!$this->type->requiresPayment() || $this->isPayed()) ? InscriptionStatusEnum::COMPLETED_SUCCESSFULLY : InscriptionStatusEnum::PENDING_PAYMENT;
            $this->save();
        }

        if ($this->inscribed_by) {
            $inscriptions = collect([$this])->merge($this->relatedInscriptions);
            // Create temporal user for the child or the registered person

            $inscriptions->each->confirmChildRegistration();
        }

        fireRegisteredEvent($user);
    }

    public function confirmChildRegistration()
    {
        if ($this->validToComplete()) {
            $this->person->createOrGetUserByRegisteredBy($this, $this->team);
        }

        $this->status = (!$this->type->requiresPayment() || $this->isPayed()) ? InscriptionStatusEnum::COMPLETED_SUCCESSFULLY : InscriptionStatusEnum::PENDING_PAYMENT;
        $this->save();
    }

    public function confirmInscriptionFilled($teamId, $event = null)
    {
        $this->status = InscriptionStatusEnum::FILLED;
        $this->event_id = $event?->id;
        $this->team_id = $teamId ?? $event?->team_id;
        $this->save();
    }

	/* ELEMENTS */
    public function statusPill()
    {
        return _Pill($this->status->label())->class($this->status->color());
    }
}
