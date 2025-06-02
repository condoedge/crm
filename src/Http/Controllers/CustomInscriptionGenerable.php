<?php

namespace Condoedge\Crm\Http\Controllers;

use Condoedge\Crm\Models\InscriptionShortLink;
use Illuminate\Routing\Controller;

class CustomInscriptionGenerable extends Controller
{
    public function __invoke()
    {
        $inscriptionLink = InscriptionShortLink::forCode(request('link_code'))->firstOrFail();

        $inscription = $inscriptionLink->createInscription();

        return redirect()->to($inscription->getRegistrationUrl());
    }
}
