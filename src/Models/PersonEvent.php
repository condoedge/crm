<?php

namespace Condoedge\Crm\Models;

use Kompo\Auth\Models\Model;

class PersonEvent extends Model
{
    use \Condoedge\Crm\Models\BelongsToPersonTrait;
    use \Condoedge\Crm\Models\BelongsToEventTrait;

    protected $casts = [
        'attendance_confirmation' => PersonEventConfirmationEnum::class,
    ];

    /* RELATIONS */

    /* SCOPES */

    /* CALCULATED FIELDS */
    public function getRelatedTargetTeam()
    {
        return $this->event->team;
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

    /* ACTIONS */
    public static function createPersonEvent($person, $event, $status = null)
    {
        $pr = static::where('person_id', $person->id)->where('event_id', $event->id)->first();

        if (!$pr) {
            $pr = new static();
            $pr->person_id = $person->id;
            $pr->event_id = $event->id;    
        }

        if ($status) {
            $pr->register_status = $status->value;
        }

        if ($pr->isDirty()) {
            $pr->save();
        }

        return $pr;
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
