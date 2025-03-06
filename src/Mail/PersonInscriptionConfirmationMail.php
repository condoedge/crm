<?php

namespace Condoedge\Crm\Mail;

use Condoedge\Crm\Facades\InscriptionModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PersonInscriptionConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $inscriptionId;
    protected $inscription;

    public function __construct($inscriptionId)
    {
        $this->inscriptionId = $inscriptionId;
        $this->inscription = InscriptionModel::findOrFail($this->inscriptionId);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(__('mail.congratulations-your-registration-is-approved'))
            ->markdown('kompo-crm::emails.inscription-confirmation-mail')
            ->with([
                'acceptInscriptionUrl' => $this->inscription->getAcceptInscriptionUrl(),
            ]);
    }
}
