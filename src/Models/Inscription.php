<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\EventModel;
use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Kompo\Inscriptions\InscriptionTypeEnum;
use Condoedge\Utils\Facades\UserModel;
use Condoedge\Utils\Models\Model;
use Kompo\Auth\Facades\RoleModel;
use Kompo\Auth\Models\Teams\BelongsToTeamTrait;

/**
 * It's used to go through the inscription process. It's one per each person and team.
 * In that way we have a record with all the details: status, type, role, etc.
 */
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

    public function person()
    {
        return $this->belongsTo(PersonModel::getClass())->throughAuthorizedRelation();
    }


    public function inscribedBy()
    {
        return $this->belongsTo(PersonModel::getClass(), 'inscribed_by')->throughAuthorizedRelation();
    }

    public function invitedBy()
    {
        return $this->belongsTo(UserModel::getClass(), 'invited_by');
    }

    public function relatedInscriptions()
    {
        return $this->hasMany(InscriptionModel::getClass(), 'related_inscription_id');
    }

    public function parentInscription()
    {
        return $this->belongsTo(InscriptionModel::getClass(), 'related_inscription_id');
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
            InscriptionStatusEnum::APPROVED,
            InscriptionStatusEnum::COMPLETED_SUCCESSFULLY,
            InscriptionStatusEnum::FILLED,
            InscriptionStatusEnum::PENDING_PAYMENT
        ]);
    }

    public function scopeConfirmed($query)
    {
        return $query->whereIn('status', InscriptionStatusEnum::getInsideStatuses());
    }

    public function scopeForScoutYear($query, $year)
    {
        return $query->whereHas('event', fn ($q) => $q->whereHas('mainTemplate', fn ($q) => $q->where('scout_year', $year)));
    }

    /* CALCULATED FIELDS */
    public function getActiveRelatedPersonTeam()
    {
        return PersonTeam::where('team_id', $this->team_id)->where('person_id', $this->person_id)->active()->first();
    }

    public function getInscriptionRoute($route, $extra = [])
    {
        return \URL::signedRoute($route, array_merge(
            ['inscription_code' => $this->getExistentQrOrCreateNew()],
            $extra
        ));
    }

    public function getInscriptionConfirmationRoute()
    {
        return $this->getInscriptionRoute('inscription.confirmation');
    }

    public function getInscriptionPersonRoute()
    {
        return $this->getRegistrationUrl();
    }

    public function isMainInscription()
    {
        return !$this->related_inscription_id;
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

    public function getMainInscription()
    {
        return $this->parentInscription ?? $this;
    }

    public function getAllRelatedInscriptions()
    {
        $mainInscription = $this->getMainInscription();
        return collect([$mainInscription, ...$mainInscription->relatedInscriptions()->with('person')->get()]);
    }

    public function getAllRelatedPersons()
    {
        return $this->getAllRelatedInscriptions()->map->person;
    }

    public function getAllRelatedNames()
    {
        return $this->getAllRelatedPersons()->map->full_name->join(', ');
    }

    public function setValueToRelatedInscriptions($key, $value)
    {
        $this->getAllRelatedInscriptions()->each->setAttribute($key, $value);
    }

    public function isApproved()
    {
        return $this->status == InscriptionStatusEnum::APPROVED;
    }

    /* ACTIONS */

    public static function getForCurrentYearQuery($personId, $inscriptionType)
    {
        return static::where('person_id', $personId)
            ->where('type', $inscriptionType->value)
            ->forScoutYear(now()->year)
            ->latest();
    }

    /**
     * This only works to get void inscriptions to be filled. Just used in the inscription process it's not created
     * to get it from other context like person profile. We need to avoid void inscriptions or creating new ones when there are voids.
     *
     * @param InscriptionTypeEnum $inscriptionType
     * @param bool $getMain Used in landing join page to get the main inscription or when we send the inscription to the person by email. It should be false when you want to get a new sibling inscription
     */
    public static function getPendingForMainPerson($personId, $teamId, $inscriptionType, $roleId = null, $getMain = null)
    {
        $inscriptionType = is_string($inscriptionType) ? getInscriptionTypes()[$inscriptionType] : $inscriptionType;

        return static::where($inscriptionType?->basedInInscriptionForOtherPerson() ? 'inscribed_by' : 'person_id', $personId)
            ->when($inscriptionType?->basedInInscriptionForOtherPerson() && !$getMain, fn ($q) => $q->whereNull('person_id'))
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->when($inscriptionType, fn ($q) => $q->where('type', $inscriptionType->value))
            ->when($roleId, fn ($q) => $q->where('role_id', $roleId))
            ->where('status', '<=', InscriptionStatusEnum::FILLED)
            ->when($getMain, fn ($q) => $q->whereNull('related_inscription_id'))
            ->first();
    }

    public static function getOrCreatePendingForMainPerson($personId, $teamId, $inscriptionType, $roleId = null, $getMain = null)
    {
        $inscriptionType = is_string($inscriptionType) ? getInscriptionTypes()[$inscriptionType] : $inscriptionType;

        if ($inscription = static::getPendingForMainPerson($personId, $teamId, $inscriptionType, $roleId, $getMain)) {
            return $inscription;
        }

        return static::createForMainPerson($personId, $teamId, $inscriptionType, $roleId);
    }

    public static function createForMainPerson($personId, $teamId, $inscriptionType, $roleId = null)
    {
        $inscriptionType = is_string($inscriptionType) ? getInscriptionTypes()[$inscriptionType] : $inscriptionType;

        $inscription = new static();
        $inscription->person_id = $inscriptionType->basedInInscriptionForOtherPerson() ? null : $personId;
        $inscription->team_id = $teamId;
        $inscription->type = $inscriptionType->value;
        $inscription->inscribed_by = $inscriptionType->basedInInscriptionForOtherPerson() ? $personId : null;
        $inscription->invited_by = auth()->id();
        $inscription->role_id = $roleId;
        $inscription->save();

        return $inscription;
    }

    public static function getReinscriptionType($person, $teamId)
    {
        $personTeam = $person->getLinkToTeam($teamId);

        return $personTeam?->inscription_type ?? null;
    }

    public function updateRegisteringPersonId($personId)
    {
        $column = $this->type->basedInInscriptionForOtherPerson() ? 'inscribed_by' : 'person_id';
        $this->setAttribute($column, $personId);

        if ($this->isDirty($column)) {
            $this->save();
        }
    }

    public function updateType($type)
    {
        $this->type = $type;

        if ($this->isDirty('type')) {
            $this->save();
        }
    }

    public function getRegistrationUrl()
    {
        if (!$this->type) {
            return route('inscription.landing', [
                'inscription_code' => $this->getExistentQrOrCreateNew(),
            ]);
        }

        if (!$this->person_id && !$this->inscribed_by) {
            return route('inscription.email.step1', [
                'inscription_code' => $this->getExistentQrOrCreateNew(),
                'type' => $this->type
            ]);
        }

        return $this->type->registerRoute($this);
    }

    public function createOrGetRegistrationUrl($personId, $teamId, $type)
    {
        $inscription = static::getOrCreatePendingForMainPerson($personId, $teamId, $type, null, true);

        return $inscription->getRegistrationUrl();
    }

    public function getInscriptionDoneRoute()
    {
        return \URL::signedRoute('inscription.done1', [
            'inscription_code' => $this->getExistentQrOrCreateNew(),
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
        if (!$this->type?->basedInInscriptionForOtherPerson()) {
            return collect();
        }

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

    public function cancelInscription()
    {
        $this->status = InscriptionStatusEnum::CANCELED;
        $this->save();

        $this->getActiveRelatedPersonTeam()?->terminate();
    }

    public function isPaid()
    {
        return true;
    }

    public function validToComplete()
    {
        return $this->canConsiderAsPaidAtInscriptionLevel() && $this->status->accepted();
    }

    public function canConsiderAsPaidAtInscriptionLevel()
    {
        return !$this->hasPendingPayment() || !static::managePaymentFromInscription();
    }

    public function confirmUserRegistration($user)
    {
        if ($this->status->completed() || !$user) {
            return;
        }

        $person = $this->person->getRegisteringPerson();

        $person->user_id = $user->id;
        $person->save();

        $roleId = $this->type->getRole($this);

        if (!$roleId) {
            abort(403, __('error.there-is-not-role-assigned-to-your-inscription'));
        }

        if ($this->type->basedInInscriptionForOtherPerson() || $this->validToComplete()) {
            $role = RoleModel::getOrCreate($this->type->getRole($this));

            $teamRole = $user->createTeamRole($this->team, $role->id);

            if (!$this->type->basedInInscriptionForOtherPerson() && ($event = $this->getEventToAttend())) {
                PersonEvent::createPersonEvent($this->person, $event);
                // $teamRole->terminated_at = $this->getExpirationDate();
                // $teamRole->save();
            }

            PersonTeam::getOrCreateForAdultInscription($this, $teamRole);
            $person->user_id = $user->id;
            $person->save();

            $this->setConfirmedStatus();
        }

        if ($this->type->basedInInscriptionForOtherPerson()) {
            $inscriptions = collect([$this])->merge($this->relatedInscriptions);
            // Create temporal user for the child or the registered person

            $inscriptions->each->confirmChildRegistration();
        }

        // fireRegisteredEvent($user);
    }

    public function markAsPaid()
    {
        $personTeams = PersonTeam::where('last_inscription_id', $this->id)->get();

        $personTeams->each->markAsPaid();

        $this->status = InscriptionStatusEnum::COMPLETED_SUCCESSFULLY;
        $this->save();
    }

    public function setConfirmedStatus()
    {
        $this->status = !$this->canConsiderAsPaidAtInscriptionLevel() ? InscriptionStatusEnum::PENDING_PAYMENT : InscriptionStatusEnum::COMPLETED_SUCCESSFULLY;
        $this->save();
    }

    public static function managePaymentFromInscription()
    {
        return config('condoedge-crm.manage-payment-from-inscription', true);
    }

    public function getExpirationDate()
    {
        return $this->type->expirationDate($this);
    }

    public function getEventToAttend()
    {
        return $this->event;
    }

    public function confirmChildRegistration()
    {
        if ($this->validToComplete()) {
            $this->person->createOrGetUserByRegisteredBy($this, $this->team);
        }

        $this->setConfirmedStatus();
    }

    public function hasPendingPayment()
    {
        return $this->type->requiresPayment() && !$this->isPaid();
    }

    public function confirmInscriptionFilled()
    {
        $this->status = InscriptionStatusEnum::FILLED;
        $this->save();
    }

    public function setSelectedTeam($teamId, $event = null)
    {
        $this->team_id = $teamId;
        $this->event_id = $event ? $event?->id : $this->event_id;
        $this->save();
    }

    public function moveToAnotherTeamAndEvent($teamId, $eventId)
    {
        $this->getActiveRelatedPersonTeam()?->moveToAnotherUnit($teamId);

        $this->event_id = $eventId;
        $this->team_id = $teamId;
        $this->save();
    }

    /* ELEMENTS */
    public function visualStatusPill()
    {
        return $this->type->statusPill($this);
    }

    public function statusPill()
    {
        return _Pill($this->status->label())->class($this->status->color())->class('text-white');
    }
}
