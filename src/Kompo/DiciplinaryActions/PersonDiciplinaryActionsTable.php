<?php

namespace Condoedge\Crm\Kompo\DiciplinaryActions;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\DiciplinaryAction;
use Condoedge\Utils\Kompo\Common\WhiteTable;

class PersonDiciplinaryActionsTable extends WhiteTable
{
    public $id = 'person-diciplinary-actions-table';

    protected $personId;
    protected $person;

    public function created()
    {
        $this->personId = $this->prop('person_id');
        $this->person = PersonModel::findOrFail($this->personId);
    }

    public function top()
    {
        return _FlexBetween(
            _TitleMain('disciplinary.disciplinary-actions'),
            _Button('disciplinary.add-new-disciplinary-action')
                ->selfCreate('getDiciplinaryActionForm')->inModal()->checkAuthWrite('DiciplinaryAction'),
        )->class('mb-4');
    }

    public function query()
    {
        return $this->person->diciplinaryActions()->with('addedBy', 'person')->latest();
    }

    public function headers()
    {
        return [
            _Th('disciplinary.effective-date'),
            _Th('disciplinary.reason'),
            _Th('disciplinary.reason-description'),
            _Th('disciplinary.added-by'),
            _Th('disciplinary.action-type'),
            _Th('')->class('w-8'),
        ];
    }

    public function render($diciplinaryAction)
    {
        return _TableRow(
            _Rows(
                _Html($diciplinaryAction->action_from->format('Y-m-d')),
                _Html($diciplinaryAction->action_to?->format('Y-m-d') ?? '-')->class('text-gray-400'),
            ),
            _Html($diciplinaryAction->action_reason_type->label()),
            _Html($diciplinaryAction->action_reason_description),
            _Html($diciplinaryAction->addedBy->name),
            _Flex(
                $diciplinaryAction->actionTypePill(),
                !$diciplinaryAction->action_to?->isPast() ? null : _Sax('check-mark')->class('text-green-600')->balloon('translate.finished'),
            )->class('gap-2'),
            _TripleDotsDropdown(
                _Link('disciplinary.edit')->class('py-1 px-2')->selfGet('getDiciplinaryActionForm', ['diciplinary_action' => $diciplinaryAction->id])->inModal(),
                $diciplinaryAction->action_to?->isPast() ? null :
                    _Link('translate.disciplinary.finish')->class('py-1 px-2')->selfPost('finishDiciplinaryAction', ['diciplinary_action' => $diciplinaryAction->id])->refresh($this->id),
            )->checkAuthWrite('DiciplinaryAction', specificModel: $diciplinaryAction),
        );
    }

    public function getDiciplinaryActionForm($diciplinaryActionId = null)
    {
        return new DiciplinaryActionForm($diciplinaryActionId, [
            'person_id' => $this->personId,
            'refresh_id' => $this->id,
        ]);
    }

    public function finishDiciplinaryAction($diciplinaryActionId)
    {
        $diciplinaryAction = DiciplinaryAction::findOrFail($diciplinaryActionId);
        $diciplinaryAction->action_to = now();
        $diciplinaryAction->save();

        $diciplinaryAction->action_type->finishedAction($diciplinaryAction);
    }
}
