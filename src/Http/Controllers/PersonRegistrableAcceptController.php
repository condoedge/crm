<?php

namespace Condoedge\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Condoedge\Crm\Facades\InscriptionModel;
use Condoedge\Crm\Models\PersonTeam;
use Kompo\Auth\Facades\RoleModel;

class PersonRegistrableAcceptController extends Controller
{
    public function __invoke($id)
    {
        $inscription = InscriptionModel::findOrFail($id);
        $email = $inscription->person->getRegisteringPersonEmail();
        $team = $inscription->team;

        if ($inscription->person->registered_by) {
            // Create temporal user for the child or the registered person

            $inscription->person->createOrGetUserByRegisteredBy($inscription, $team);
        }

        if ($user = User::where('email', $email)->first()) {
            
            if (!$team->hasUserWithEmail($email)) {
                $roleId = $inscription->type->getRole($inscription);

                if(!$roleId) {
                    abort(403, __('error.there-is-not-role-assigned-to-your-inscription'));
                }

                RoleModel::getOrCreate($roleId);
                
                $teamRole = $user->createTeamRole($team, role: $roleId);
                
                // Get or create the personTeam
                $personTeam = PersonTeam::where('person_id', $inscription->person->getRegisteringPerson()->id)->where('team_id', $team->id)->whereNull('team_role_id')->first();

                if (!$personTeam) {
                    $personTeam =  PersonTeam::createFromTeamRole($teamRole);
                } else {
                    $personTeam->team_role_id = $teamRole->id;
                }

                $personTeam->inscription_type = $inscription->type?->value;
                $personTeam->save();
            }

            return redirect()->route('login.password', ['email' => $email]);

        } else {

            return redirect()->to($inscription->getPerformRegistrationUrl());
        }
    }
}
