<?php

namespace Condoedge\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Condoedge\Crm\Facades\InscriptionModel;

class PersonRegistrableAcceptController extends Controller
{
    public function __invoke($id)
    {
        $inscription = InscriptionModel::findOrFail($id);

        // 422, as the create-account page answers: a 403 page does not print the reason.
        if ($reason = $inscription->deadLinkReason()) {
            abort(422, $reason);
        }

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
