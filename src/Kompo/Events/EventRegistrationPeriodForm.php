<?php

namespace Condoedge\Crm\Kompo\Events;

use App\Models\Events\Event;
use Condoedge\Crm\Models\EventRegistrationPeriod;
use Condoedge\Crm\Models\RegistrationTypeEnum;
use Kompo\Auth\Common\ModalScroll;

class EventRegistrationPeriodForm extends ModalScroll
{
    public $model = EventRegistrationPeriod::class;

    protected $eventId;
    protected $event;

    public function created()
    {
        $this->eventId = $this->prop('event_id');
        $this->event = Event::findOrFail($this->eventId);
    }

    public function beforeSave()
    {
        $this->model->event_id = $this->eventId;
    }

    public function render()
    {
        return _Rows(
                _Input('inscriptions.title')->name('registration_name')
                    ->default(__('inscriptions.registrations').' '.$this->event->name_ev),
                _Columns(
                    _Select('inscriptions.registration-type')->name('registration_type')->options(RegistrationTypeEnum::optionsWithLabels()),
                ),
                _CardLevel5(
                    _Columns(
                        _DateTime('inscriptions.registration-period-start')->name('registration_start'),
                        _DateTime('inscriptions.registration-period-end')->name('registration_end'),
                    ),
                )->class('px-6 pb-2'),
                _InputDollar('inscriptions.amount-for-registration')->name('registration_price'),
                _Input('inscriptions.number-of-participants')->name('registration_max_members'),
            _SubmitButton('inscriptions.save'),
        )->class('p-8');
    }

    public function rules()
    {
        return [
            'registration_name' => 'required',
            'registration_start' => 'required',
            'registration_end' => 'required',
            'registration_price' => 'required',
            'registration_max_members' => 'required',
        ];
    }
}
