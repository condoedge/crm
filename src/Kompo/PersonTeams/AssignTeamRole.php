<?php

namespace Condoedge\Crm\Kompo\PersonTeams;

use Condoedge\Crm\Facades\PersonTeamModel;
use Kompo\Auth\Teams\Roles\AssignRoleModal as BaseModal;

class AssignTeamRole extends BaseModal
{
    protected $refreshId = 'person-roles-table';
    protected $permissionKey = 'PersonTeam';

    public function afterSave()
    {
        PersonTeamModel::createFromTeamRole($this->model, startDate: request('from'));
    }

    public function extraInputs()
    {
        return [
            // from when the person had this role
            _Date('crm.start-date')->name('from', false)->required()
                ->default(now()),
        ];
    }

    public static function terminateTeamRole($teamRole)
    {
        $teamRole->terminate();

        PersonTeamModel::where('team_role_id', $teamRole->id)
            ->get()->each->terminate();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            'start_date' => 'required|date|before_or_equal:today',
        ]);
    }
}
