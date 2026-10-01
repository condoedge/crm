<?php

namespace Condoedge\Crm\Kompo\Inscriptions\GenericForms;

use Kompo\Auth\Models\Teams\EmailRequest;

trait PersonFormUtilsTrait
{
    protected function verifyEmail()
    {
        // Email is not required on any step, so a registrant may reach one without an address to verify.
        if (!$this->model?->email_identity) {
            return;
        }

        $emailRequest = EmailRequest::getOrCreateEmailRequest($this->model->email_identity);
        $emailRequest->markEmailAsVerified();
    }
}
