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
        $email = $inscription->person->email_identity;
        $team = $inscription->team_id;

        if ($user = User::where('email', $email)->first()) {
            
            if (!$team->hasUserWithEmail($email)) {
                $role = $inscription->type->getRole($inscription);
                RoleModel::getOrCreate($role);
                
                $user->createTeamRole($team, $role);
            }

            return redirect()->route('login.password', ['email' => $email]);

        } else {

            return redirect()->to($inscription->getPerformRegistrationUrl());
        }
    }
}
