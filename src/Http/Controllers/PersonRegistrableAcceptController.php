<?php

namespace Condoedge\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Roles\ParentRole;
use App\Models\User;
use Condoedge\Crm\Models\PersonRegistrable;

class PersonRegistrableAcceptController extends Controller
{
    public function __invoke($id)
    {
        $pr = PersonRegistrable::findOrFail($id);
        $email = $pr->getRelatedEmail();
        $team = $pr->getRelatedTargetTeam();

        if ($user = User::where('email', $email)->first()) {
            
            if (!$team->hasUserWithEmail($email)) {
                $user->createTeamRole($team, ParentRole::ROLE_KEY);
            }

            return redirect()->route('login.password', ['email' => $email]);

        } else {

            return redirect()->to($pr->getPerformRegistrationUrl());
        }
    }
}
