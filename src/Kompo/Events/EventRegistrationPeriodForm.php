<?php

namespace Condoedge\Crm\Kompo\Events;

use App\Models\Events\Event;
use Condoedge\Crm\Models\EventRegistrationPeriod;
use Condoedge\Crm\Models\RegistrationTypeEnum;
use Condoedge\Utils\Kompo\Common\Modal;

class EventRegistrationPeriodForm extends Modal
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
            _Input('inscriptions.title')->name('registration_name')->required()
                    ->default(__('inscriptions.registrations').' '.$this->event->name_ev),
            _Columns(
                _Select('inscriptions.registration-type')->name('registration_type')->options(RegistrationTypeEnum::optionsWithLabels()),
            ),
            _CardLevel5(
                _Columns(
                    _DateTime('inscriptions.registration-period-start')->name('registration_start')->required(),
                    _DateTime('inscriptions.registration-period-end')->name('registration_end')->required(),
                ),
            )->class('px-6 pb-2'),
            _InputDollar('inscriptions.amount-for-registration')->name('registration_price')->required(),
            _Input('inscriptions.number-of-participants')->name('registration_max_members')->required(),
            _SubmitButton('inscriptions.save'),
        )->class('p-8');
    }

    public function rules()
    {
        return [
            'registration_name' => 'required',
            'registration_start' => 'required',
            'registration_end' => 'required',
            'registration_price' => 'required|min:0',
            'registration_max_members' => 'required|min:0',
        ];
    }
}
