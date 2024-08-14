<?php

namespace Condoedge\Crm\Kompo\DiciplinaryActions;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\DiciplinaryAction;
use Condoedge\Crm\Models\DiciplinaryActionTypeEnum;
use Condoedge\Crm\Models\DiciplinaryReasonTypeEnum;
use Condoedge\Crm\Models\Person;
use Kompo\Auth\Common\Modal;

class DiciplinaryActionForm extends Modal
{
    public $model = DiciplinaryAction::class;

    protected $personId;
    protected $person;
    protected $specificAction;
    protected $noHeaderButtons = true;

    public function created()
    {
        $this->specificAction = is_numeric($this->prop('specific_action')) ? DiciplinaryActionTypeEnum::from($this->prop('specific_action')) : null;
        $this->_Title = $this->specificAction?->formTitle() ?? 'translate.add-diciplinary-action';

        $this->personId = $this->prop('person_id');
        $this->person = $this->personId ? PersonModel::findOrFail($this->personId) : null;
    }

    public function afterSave()
    {
        if($this->model->action_from->isToday()) {
            $this->model->action_type->startedAction($this->model);
        }

        if($this->model->action_to?->isToday() || $this->model->action_to?->isPast()) {
            $this->model->action_type->finishedAction($this->model);
        }
    }

    public function body()
    {
        return _Rows(
            _Select('translate.member')->name('person_id')
                ->default($this->person?->id)
                ->options([$this->person?->id => $this->person?->full_name])->name('person_id')
                ->attr(['disabled' => 'disabled']),
            _Date('translate.effective-from')->name('action_from')
                ->default(now()->format('Y-m-d')),
            _Date('translate.effective-to')->name('action_to'),

            _Select('translate.action')->name('action_type')
                ->when($this->specificAction, fn($select) => $select->class('hidden')->default($this->specificAction->value))
                ->options(DiciplinaryActionTypeEnum::optionsWithLabels()),

            _Select('translate.added-by')->name('added_by')
                ->default(auth()->id())
                ->options([auth()->id() => auth()->user()->name]),

            _Select('translate.reason')->name('action_reason_type')->options(DiciplinaryReasonTypeEnum::optionsWithLabels()),
            _Textarea('translate.reason-description')->name('action_reason_description'),

            _SubmitButton('generic.save'),
        );
    }

    public function rules()
    {
        return [
            'action_type' => 'required|in:' . collect(DiciplinaryActionTypeEnum::cases())->keys()->implode(','),
            'action_from' => 'required|date',
            'action_to' => 'nullable|date|after:action_from',
            'action_reason_type' => 'required|in:' . collect(DiciplinaryReasonTypeEnum::cases())->keys()->implode(','),

            'person_id' => 'required|exists:persons,id',
            'added_by' => 'required|exists:users,id',
        ];
    }
}