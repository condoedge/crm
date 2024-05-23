<?php

namespace Condoedge\Crm\Mail;

use Condoedge\Crm\Models\PersonRegistrable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PersonInscriptionConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $personRegistrableId;
    protected $personRegistrable;

    public function __construct($personRegistrableId)
    {
        $this->personRegistrableId = $personRegistrableId;
        $this->personRegistrable = PersonRegistrable::findOrFail($this->personRegistrableId);
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
                'acceptInscriptionUrl' => $this->personRegistrable->getAcceptInscriptionUrl(),
            ]);
    }
}
