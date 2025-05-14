<?php

namespace Condoedge\Crm\Kompo\PersonTeams;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\PersonTeam;
use Condoedge\Utils\Kompo\Common\Table;
use Kompo\Auth\Models\Teams\PermissionTypeEnum;

class PersonTeamsWithRolesTable extends Table
{
    const ID = 'person-roles-table';
    public $id = self::ID;

    public $personId;
    protected $person;

    public function created()
    {
        $this->personId = $this->prop('person_id');
        $this->person = PersonModel::findOrFail($this->personId);
    }

    public function top()
    {
        return _FlexEnd(
            _Toggle('permissions.show-inactive')->name('show_all', false)->filter()
                ->class('[&>.vlFormLabel]:w-max !mb-0'),
            _Dropdown('permissions.actions')->button()
                ->submenu(
                    _Link('permissions.assign-role')->class('py-1 px-3')->selfGet('getAssignRoleModal')->inModal(),
                )->checkAuth('TeamRole', null, PermissionTypeEnum::WRITE),
        )->class('mb-3 gap-6 items-center');
    }

    public function query()
    {
        return $this->person->personTeams()
            ->when(!request('show_all'), fn($q) => $q->active())
            ->orderByDesc('from')
            ->with([
                'team', 
                'teamRole' => fn($q) => $q->withoutGlobalScopes(), // Here is only visual and we need to get the terminated roles so we remove the global scope
                'teamRole.roleRelation',
            ]);
    }

    public function headers()
    {
        return [
            _Th('permissions.role'),
            _Th('permissions.team'),
            _Th('permissions.date'),
            _Th('permissions.status'),
            _Th()->class('w-8'),
        ];
    }

    public function render($personTeam) 
    {
        return _TableRow(
            _Html($personTeam->teamRoleIncludingDeleted?->roleRelation?->name ?: '-')->class('font-semibold'),
            $personTeam->team->getFullInfoTableElement(),
            _Rows(
                _Html($personTeam->from->format('d/m/Y')),
                _Html($personTeam->to?->format('d/m/Y'))->class('text-gray-400'),
            ),
            $personTeam->teamRoleIncludingDeleted?->statusPill() ?? $personTeam->getStatusPillElement(),
            _Html(),

            _TripleDotsDropdown(
                _DeleteLink('permissions.delete')->class('py-1 px-3 text-danger rounded-md')->byKey($personTeam),
                ($personTeam->teamRoleIncludingDeleted && !$personTeam->teamRoleIncludingDeleted->terminated_at || !$personTeam->to) 
                    ? _DropdownLink('permissions.terminate')->class('py-1 px-3 justify-end rounded-md')->selfPost('terminateRole', ['team_role_id' => $personTeam->id])->browse()
                    : null,
            )->class('text-right')->checkAuth('TeamRole', $personTeam->team_id, PermissionTypeEnum::WRITE),
        );
    }

    public function terminateRole($personTeamId)
    {
        $teamRole = PersonTeam::findOrFail($personTeamId);
        $teamRole->terminate();
    }

    public function getAssignRoleModal()
    {
        if (!$this->person->relatedUser) {
            // Here we could open a modal to set a new personTeam without teamRole
            return _CardWhiteP4(_Html('permissions.user-not-linked'));
        }

        return new (config('kompo-auth.assign-role-modal-namespace'))([
            'user_id' => $this->person->relatedUser?->id,
        ]);
    }
}