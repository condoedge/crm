<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\InscriptionModel;
use Kompo\Auth\Models\Model;

class PersonEvent extends Model
{
    use \Condoedge\Crm\Models\BelongsToPersonTrait;
    use \Condoedge\Crm\Models\BelongsToEventTrait;

    protected $casts = [
        'register_status' => RegisterStatusEnum::class,
        'attendance_confirmation' => PersonEventConfirmationEnum::class,
    ];

    /* RELATIONS */
    public function inscription()
    {
        return $this->belongsTo(InscriptionModel::getClass());
    }

    /* SCOPES */
    public function scopeCountInTotal($query)
    {
        $query->whereIn('register_status', [
            RegisterStatusEnum::RS_ACCEPTED,
            RegisterStatusEnum::RS_PAID,
        ]);
    }

    public function scopeAwaitingApproval($query)
    {
        $query->whereIn('register_status', [
            RegisterStatusEnum::RS_REQUESTED,
        ]);
    }

    /* CALCULATED FIELDS */
    public function getIeStatusLabelAttribute()
    {
        return $this->register_status->label();
    }

    public function getRegisteringPerson()
    {
        return $this->person->registeredBy ?: $this->person;
    }

    public function getRegisteringPersonEmail()
    {
        return $this->getRegisteringPerson()->email_identity;
    }

    public function getRelatedTargetTeam()
    {
        return $this->event->team;
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
        return static::where('inscription_id', $this->inscription_id)->get();
    }

    public function getAttendance()
    {
        return EventAttendance::forPersonEvent($this->id)->first();
    }

    public function isAttended()
    {
        return EventAttendance::forPersonEvent($this->id)
            ->where('attendance_status', EventAttendanceStatus::ATTENDED)
            ->exists();
    }

    public function isAbstent()
    {
        return EventAttendance::forPersonEvent($this->id)
            ->where('attendance_status', EventAttendanceStatus::ABSTENT)
            ->exists();
    }

    /* ROUTES */
    public function getAcceptInscriptionUrl()
    {
        $inscription = $this->inscription ?: InscriptionModel::getOrCreateForPerson($this->person_id, $this->event->team_id, InscriptionModel::defaultTypeForPersonEvent());

        if (!$inscription->team_id) {
            $inscription->team_id = $this->event->team_id;
            $inscription->save();
        }

        return $inscription->getAcceptInscriptionUrl();
    }

    public function getInscriptionDoneRoute()
    {
        return \URL::signedRoute('inscription.done1', [
            'id' => $this->id,
        ]);
    }

    /* ACTIONS */
    public static function createPersonEvent($person, $event, $inscription = null)
    {
        $pr = new static();
        $pr->person_id = $person->id;
        $pr->event_id = $event->id;
        $pr->inscription_id = $inscription?->id;
        $pr->save();

        return $pr;
    }

    public function approveAndSend()
    {
        $this->register_status = RegisterStatusEnum::RS_ACCEPTED;
        $this->save();

        $this->approveAndSendCustomCode();

        // \Mail::to($this->getRegisteringPersonEmail())
        //     ->send(new \Condoedge\Crm\Mail\PersonInscriptionConfirmationMail($this->id));
    }

    protected function approveAndSendCustomCode()
    {
        //Override in App
    }

    public function toggleAttendance()
    {
        $attendance = $this->getAttendance();
        $nextAttendance = $attendance?->attendance_status?->nextCheck() ?? EventAttendanceStatus::ATTENDED;

        EventAttendance::takeAttendanceFromPersonEvent($this->id, $nextAttendance);
    }

    /* ELEMENTS */
    public function attendanceConfirmationPill()
    {
        $status = $this->attendance_status;

        if (!$status) {
            return null;
        }

        return _Pill($status->label())->class($status->classes());
    }

    public function attendancePill()
    {
        $status = $this->getAttendance()?->attendance_status ?? EventAttendanceStatus::NOT_TAKEN;

        return _Pill($status->label())->class($status->classes())->class('!text-base !rounded-lg !px-2 py-1');
    }
}
