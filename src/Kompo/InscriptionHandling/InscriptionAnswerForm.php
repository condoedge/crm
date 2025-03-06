<?php

namespace Condoedge\Crm\Kompo\InscriptionHandling;

use Condoedge\Crm\Facades\InscriptionModel;
use Kompo\Auth\Common\ModalScroll;

class InscriptionAnswerForm extends ModalScroll
{
    use \Condoedge\Crm\Kompo\Inscriptions\GenericForms\InscriptionFormUtilsTrait;

    public $model = InscriptionModel::class;

    protected $otherEventsOptions;

    protected $_Title = 'inscriptions.approve-registration-for';
    protected $noHeaderButtons = true;

    public function created()
    {
        $this->setInscriptionInfo();

        $this->otherEventsOptions = collect();
    }

    public function handle()
    {
        $this->model->event_id = request('change_to_event_id');
        $this->model->save();
        
        return $this->approvePersonToEvent();
    }

    public function approvePersonToEvent()
    {        
        $this->model->acceptInscription();

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
            !$this->otherEventsOptions->count() ? null : _Rows(
                _Html('inscriptions.-or-')->class('text-center mb-4'),
                _CardGray100P4(
                    _TitleModal('inscriptions.move-to-another-unit'),
                    _Select()->name('change_to_event_id', false)
                        ->options($this->otherEventsOptions),
                    _SubmitButton('inscriptions.move-to-this-unit'),
                ),
            )->class('pb-10')
        );
    }

    public function rules()
    {
        return [
            //'change_to_team_id' => 'required',
        ];
    }
}
