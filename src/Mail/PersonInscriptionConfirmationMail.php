<?php

namespace Condoedge\Crm\Mail;

use Condoedge\Crm\Models\PersonEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PersonInscriptionConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $personEventId;
    protected $personEvent;

    public function __construct($personEventId)
    {
        $this->personEventId = $personEventId;
        $this->personEvent = PersonEvent::findOrFail($this->personEventId);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(__('mail.congratulations-your-registration-is-approved'))
            ->markdown('emails.inscription-confirmation-mail')
            ->with([
                'acceptInscriptionUrl' => $this->personEvent->getAcceptInscriptionUrl(),
            ]);
    }
}
