<?php

namespace Condoedge\Crm\Kompo\InscriptionHandling;

use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Utils\Kompo\Common\WhiteTable;

class InscriptionsList extends WhiteTable
{
    protected $eventId;

    public function created()
    {
        $this->eventId = $this->prop('event_id');
    }

    protected function queryForEvent()
    {
        return InscriptionModel::where('event_id', $this->eventId);
    }

    public function query()
    {
        return $this->queryForEvent()->latest()->with('person', 'event.team');
    }

    public function top()
    {
        return _Rows(
            _FlexBetween(
                _TitleMain('inscriptions.registrations'),
                _FlexEnd4(
                    _CardIconStat('tick-circle', 'inscriptions.nb-registered', _Html($this->queryForEvent()->countsInTotal()->count()))->class('bg-level1 text-white'),
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

    public function render($inscription)
    {
        $person = $inscription->person;

        return _TableRow(
            _Rows(
                _Html($person->full_name),
                _Html($person->age_label),
            ),
            _Pill($person->gender_label),
            _Html($inscription->team->team_name),
            _HtmlDate($inscription->created_at),
            _Pill($inscription->status->label()),
        )->selfUpdate('getInscriptionAnswerForm', [
            'id' => $inscription->id,
        ])->inModal();
    }

    public function getInscriptionAnswerForm($id)
    {
        return new InscriptionAnswerForm($id);
    }
}
