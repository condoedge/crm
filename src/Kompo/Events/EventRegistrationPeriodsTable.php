<?php

namespace Condoedge\Crm\Kompo\Events;

use Condoedge\Crm\Models\EventRegistrationPeriod;
use Kompo\Table;

class EventRegistrationPeriodsTable extends Table
{
    protected $eventId;

    public function created()
    {
        $this->eventId = $this->prop('event_id');
    }

    public function query()
    {
        return EventRegistrationPeriod::forEvent($this->eventId)->latest();
    }

    public function top()
    {
        return _FlexBetween(
            _Html('Registration periods')->class('text-2xl font-semibold'),
            _Button('add')->selfCreate('getItemForm')->inModal(),
        );
    }

    public function headers()
    {
        return [
            _Th('Registration'),
            _Th('Starts at'),
            _Th('Ends at'),
        ];
    }

    public function render($erp)
    {
        return _TableRow(
            _Html($erp->registration_name),
            _Html($erp->addedBy->name),
            _Html($erp->date_nt->format('Y-m-d H:i')),
        )->selfUpdate('getItemForm', ['id' => $erp->id])->inModal();
    }

    public function getItemForm($id = null)
    {
        return new EventRegistrationPeriodForm($id, [
            'event_id' => $this->eventId,
        ]);
    }
}