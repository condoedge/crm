<?php

namespace Condoedge\Crm\Models;

use Condoedge\Crm\Facades\PersonModel;
use Kompo\Auth\Models\Model;

class EventAttendance extends Model
{
    use \Condoedge\Crm\Models\BelongsToEventTrait;

    protected $casts = [
        'attendance_status' => EventAttendanceStatus::class,
    ];

    /* RELATIONS */

    /* SCOPES */
    public function scopeForPersonEvent($query, $personEventId)
    {
        $personEvent = PersonEvent::findOrFail($personEventId);

        return $query->where('event_id', $personEvent->event_id)
            ->where('attendable_id', $personEvent->person_id)
            ->where('attendable_type', PersonModel::getActualClassNameForMorph(PersonModel::getClass()));
    }

    /* CALCULATED FIELDS */

    /* ACTIONS */
    public static function takeAttendanceFromPersonEvent($personEventId, $attendanceStatus)
    {
        $attendance = self::forPersonEvent($personEventId)->first();

        if (!$attendanceStatus || $attendanceStatus == EventAttendanceStatus::NOT_TAKEN) {
            $attendance?->delete();

            return;
        }

        if (!$attendance) {
            $personEvent = PersonEvent::findOrFail($personEventId);

            $attendance = new static();
            $attendance->event_id = $personEvent->event_id;
            $attendance->attendable_id = $personEvent->person_id;
            $attendance->attendable_type = PersonModel::getActualClassNameForMorph(PersonModel::getClass());
        }

        $attendance->attendance_status = $attendanceStatus;
        $attendance->save();
    }

    /* ELEMENTS */
}
