<?php

namespace Condoedge\Crm\Kompo\PersonTeams;

use Condoedge\Crm\Facades\PersonTeamModel;
use Kompo\Auth\Teams\Roles\AssignRoleModal as BaseModal;

class AssignTeamRole extends BaseModal
{
    protected $refreshId = 'person-roles-table';

    public function afterSave()
    {
        PersonTeamModel::createFromTeamRole($this->model);
    }

    public static function terminateTeamRole($teamRole)
    {
        $teamRole->terminate();

        PersonTeamModel::where('team_role_id', $teamRole->id)
            ->get()->each->terminate();
    }
}
