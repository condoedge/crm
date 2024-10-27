<?php

namespace Condoedge\Crm\Kompo\Inscriptions\GenericForms;

use Kompo\Auth\Models\Teams\EmailRequest;

trait PersonFormUtilsTrait 
{
    protected function verifyEmail()
    {
        $emailRequest = EmailRequest::getOrCreateEmailRequest($this->model->email_identity);
		$emailRequest->markEmailAsVerified();
    }
}