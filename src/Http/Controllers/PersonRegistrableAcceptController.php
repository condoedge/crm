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

        if (!$inscription->status->accepted() || $inscription->status->completed()) {
            if (auth()->user()) {
                return redirect()->to(route('dashboard'));
            }

            return redirect()->to(route('login.password', ['email' => $inscription->person->email_identity]));
        }

        $email = $inscription->person->getRegisteringPersonEmail();

        if ($user = User::where('email', $email)->first()) {
            
            $inscription->confirmUserRegistration($user);

            return redirect()->to(route('login.password', ['email' => $email]));

        } else {
            return redirect()->to($inscription->getPerformRegistrationUrl());
        }
    }
}
