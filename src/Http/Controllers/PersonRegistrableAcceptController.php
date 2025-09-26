<?php

namespace Condoedge\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Condoedge\Crm\Facades\InscriptionModel;

class PersonRegistrableAcceptController extends Controller
{
    public function __invoke($id)
    {
        $inscription = InscriptionModel::findOrFail($id);
        $user = $inscription->getRegisteringRelatedUser();

        if ($user) {
            $inscription->confirmInscriptionAsUserIfRegistered();
            auth()->login($user);

            return redirect()->to(route('dashboard'));
        } else {
            return redirect()->to($inscription->getPerformRegistrationUrl());
        }
    }
}
