<?php

namespace Condoedge\Crm\Kompo\InscriptionHandling;

use App\Models\Crm\PersonEvent;
use Kompo\Auth\Common\ModalScroll;

class PersonEventAnswerForm extends ModalScroll
{
    public $model = PersonEvent::class;

    protected $person;
    protected $event;
    protected $team;
    protected $otherUnitsOptions;

    protected $_Title = 'inscriptions.approve-registration-for';
    protected $noHeaderButtons = true;

    public function created()
    {
        $this->person = $this->model->person;
        $this->event = $this->model->event;
        $this->team = $this->event?->team;

        $this->otherUnitsOptions = $this->team->parentTeam->teams()->pluck('team_name', 'id');
    }

    public function handle()
    {
        $this->model->team_id = request('change_to_team_id');
        $this->model->save();
        
        return $this->approvePersonToEvent();
    }

    public function approvePersonToEvent()
    {        
        $this->model->approveAndSend();

        return redirect()->route('person-events.list', [
            'event_id' => $this->model->event_id,
        ]);
    }

    public function body()
    {
        return _Rows(
            _Rows(
                _TitleModal($this->team?->team_name),
                _CardLevel4(
                    _TitleMini($this->person->full_name),
                    _Flex4(
                        _Html($this->person->age_label),
                        _Html($this->person->gender_label),
                    ),
                )->p4(),

                _Button('inscriptions.accept')->selfPost('approvePersonToEvent')->redirect(),
            )->class('space-y-4'),
            /*_Rows(
                _Html(' --- or ---')->class('text-center mb-4'),
                _CardGray100P4(
                    _TitleModal('Move to another unit'),
                    _Select()->name('change_to_team_id', false)
                        ->options($this->otherUnitsOptions),
                    _SubmitButton('Move to this unit'),
                ),
            )->class('pb-10')*/
        );
    }

    public function rules()
    {
        return [
            //'change_to_team_id' => 'required',
        ];
    }
}
