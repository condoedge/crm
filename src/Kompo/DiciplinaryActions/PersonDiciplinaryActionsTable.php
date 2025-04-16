<?php

namespace Condoedge\Crm\Kompo\DiciplinaryActions;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\DiciplinaryAction;
use Condoedge\Utils\Kompo\Common\WhiteTable;

class PersonDiciplinaryActionsTable extends WhiteTable
{
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
            _Button('disciplinary.add-new-disciplinary-action')->selfCreate('getDiciplinaryActionForm')->inModal(),
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
            $diciplinaryAction->actionTypePill(),

            _TripleDotsDropdown(
                _Link('disciplinary.edit')->class('py-1 px-2')->selfGet('getDiciplinaryActionForm', ['diciplinary_action' => $diciplinaryAction->id])->inModal(),
                _Link('disciplinary.finish')->class('py-1 px-2')->selfPost('finishDiciplinaryAction', ['diciplinary_action' => $diciplinaryAction->id])->refresh(),
            ),
        );
    }

    public function getDiciplinaryActionForm($diciplinaryActionId = null)
    {
        return new DiciplinaryActionForm($diciplinaryActionId, [
            'person_id' => $this->personId,
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