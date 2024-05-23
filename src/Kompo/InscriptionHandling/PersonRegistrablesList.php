<?php

namespace Condoedge\Crm\Kompo\InscriptionHandling;

use Condoedge\Crm\Models\PersonRegistrable;
use Kompo\Auth\Common\WhiteTable;

class PersonRegistrablesList extends WhiteTable
{
    protected $activityId;
    protected $eventId;

    public function created()
    {
        $this->eventId = $this->prop('event_id');
        $this->activityId = $this->prop('activity_id');
    }

    protected function queryForEvent()
    {
        return PersonRegistrable::forEvent($this->eventId)->forActiviy($this->activityId);
    }

    public function query()
    {
        return $this->queryForEvent()->latest()->with('person', 'activity.team', 'event.activity.team');
    }

    public function top()
    {
        return _Rows(
            _FlexBetween(
                _TitleMain('inscriptions.registrations'),
                _FlexEnd4(
                    _CardIconStat('tick-circle', 'inscriptions.nb-registered', _Html($this->queryForEvent()->countInTotal()->count()))->class('bg-level1 text-white'),
                    _CardIconStat('tick-circle', 'inscriptions.pending', _Html($this->queryForEvent()->awaitingApproval()->count()))->class('bg-level2 text-white'),
                ),
            )->class('mb-4'),

        );
    }

    public function headers()
    {
        return [
            _Th('inscriptions.members'),
            _Th('inscriptions.gender'),
            _Th('inscriptions.registered-to-unit'),
            _Th('inscriptions.registration-date'),
            _Th('inscriptions.status'),
        ];
    }

    public function render($pr)
    {
        $person = $pr->person;

        return _TableRow(
            _Rows(
                _Html($person->full_name),
                _Html($person->age_label),
            ),
            _Pill($person->gender_label),
            _Html($pr->getRegistrable()->getTargetTeam()->team_name),
            _HtmlDate($pr->created_at),
            _Pill($pr->ie_status_label),
        )->selfUpdate('getPersonRegistrableAnswerForm', [
            'id' => $pr->id,
        ])->inModal();
    }

    public function getPersonRegistrableAnswerForm($id)
    {
        return new PersonRegistrableAnswerForm($id);
    }
}
