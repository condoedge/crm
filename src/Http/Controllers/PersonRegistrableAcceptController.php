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

        if (!$inscription->status->accepted() || $inscription->status->completed()) return;

        $email = $inscription->person->getRegisteringPersonEmail();

        if ($user = User::where('email', $email)->first()) {
            
            $inscription->confirmUserRegistration($user);

            return redirect()->to(route('login.password', ['email' => $email]));

        } else {
            return redirect()->to($inscription->getPerformRegistrationUrl());
        }
    }
}
