<?php

namespace Condoedge\Crm\Kompo\PersonTeams;

use Condoedge\Crm\Models\PersonTeam;
use Kompo\Auth\Teams\Roles\AssignRoleModal as BaseModal;

class AssignTeamRole extends BaseModal
{
    protected $refreshId = 'person-roles-table';
    
    public function afterSave()
    {
        PersonTeam::createFromTeamRole($this->model);
    }
}