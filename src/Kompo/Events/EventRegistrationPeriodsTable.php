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
            _Link()->iconCreate()->selfCreate('getItemForm')->inModal(),
        );
    }

    public function render($erp)
    {
        return _TableRow(
            _Html($erp->registration_name),
            _HtmlDateTime($erp->registration_start),
            _HtmlDateTime($erp->registration_end),
            _Link()->iconSax('barcode')->selfGet('displayQrCode', ['id' => $erp->id])->inModal(),
        )->selfUpdate('getItemForm', ['id' => $erp->id])->inModal();
    }

    public function getItemForm($id = null)
    {
        return new EventRegistrationPeriodForm($id, [
            'event_id' => $this->eventId,
        ]);
    }

    public function displayQrCode($id)
    {
        $erp = EventRegistrationPeriod::findOrFail($id);

        return $erp->getDisplayableQrInfoEls();
    }
}