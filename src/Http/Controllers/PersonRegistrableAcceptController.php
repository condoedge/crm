<?php

namespace Condoedge\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Condoedge\Crm\Facades\InscriptionModel;

class PersonRegistrableAcceptController extends Controller
{
    public function __invoke($id)
    {
        $inscription = InscriptionModel::findOrFail($id);
        $email = $inscription->person->getRegisteringPersonEmail();
        $user = User::where('email', $email)->first();

        if (!$inscription->status->accepted()) {
            throw new \Exception('Inscription is not accepted');
        }

        if (!$inscription->status->completed() && $user) {
            $inscription->confirmUserRegistration($user);
        }

        if ($user) {
            auth()->login($user);

            return redirect()->to(route('dashboard'));
        } else {
            return redirect()->to($inscription->getPerformRegistrationUrl());
        }
    }
}
