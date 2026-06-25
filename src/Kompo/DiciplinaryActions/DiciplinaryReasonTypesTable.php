<?php

namespace Condoedge\Crm\Kompo\DiciplinaryActions;

use Condoedge\Crm\Models\DiciplinaryReasonType;
use Condoedge\Utils\Kompo\Common\Table;

class DiciplinaryReasonTypesTable extends Table
{
    protected $teamId;

    public function created()
    {
        $this->teamId = $this->prop('team_id');
    }

    public function query()
    {
        return DiciplinaryReasonType::forTeams([$this->teamId])->orderBy('name');
    }

    public function top()
    {
        return _FlexBetween(
            _Html('crm.disciplinary-reason-types')->class('text-2xl'),
            _Button('crm.add-disciplinary-reason-type')
                ->icon('plus')
                ->selfCreate('getDiciplinaryReasonTypeForm')
                ->inModal()
        )->class('gap-4');
    }

    public function headers()
    {
        return [
            _Th('crm.name'),
            _Th('crm.description'),
            _Th()->class('text-right w-8'),
        ];
    }

    public function render($disciplinaryReasonType)
    {
        return _TableRow(
            _Html($disciplinaryReasonType->name),
            _Html($disciplinaryReasonType->description),
            
            _Delete($disciplinaryReasonType),
        )->selfUpdate('getDiciplinaryReasonTypeForm', ['id' => $disciplinaryReasonType->id])->inModal();
    }

    public function getDiciplinaryReasonTypeForm($id = null)
    {
        return new DiciplinaryReasonTypeForm($id, [
            'team_id' => $this->teamId,
        ]);
    }
}