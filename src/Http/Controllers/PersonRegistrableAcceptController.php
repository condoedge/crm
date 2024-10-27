<?php

namespace Condoedge\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Condoedge\Crm\Facades\InscriptionModel;
use Kompo\Auth\Facades\RoleModel;

class PersonRegistrableAcceptController extends Controller
{
    public function __invoke($id)
    {
        $inscription = InscriptionModel::findOrFail($id);
        $email = $inscription->person->getRegisteringPersonEmail();
        $team = $inscription->team_id;

        if ($inscription->person->registered_by) {
            // Create temporal user

            $inscription->person->createOrGetUserByRegisteredBy($inscription, $team);
        }

        if ($user = User::where('email', $email)->first()) {
            
            if (!$team->hasUserWithEmail($email)) {
                $roleId = $inscription->type->getRole($inscription);
                RoleModel::getOrCreate($roleId);
                
                $user->createTeamRole($team, $roleId);
            }

            return redirect()->route('login.password', ['email' => $email]);

        } else {

            return redirect()->to($inscription->getPerformRegistrationUrl());
        }
    }
}
