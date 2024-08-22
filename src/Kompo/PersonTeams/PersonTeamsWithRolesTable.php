<?php

namespace Condoedge\Crm\Kompo\PersonTeams;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\PersonTeam;
use Kompo\Auth\Models\Teams\TeamRole;
use Kompo\Auth\Common\Table;

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
            _Dropdown('translate.actions')->button()
                ->submenu(
                    _Link('translate.assign-role')->class('py-1 px-3')->selfGet('getAssignRoleModal')->inModal(),
                ),
        )->class('mb-3');
    }

    public function query()
    {
        return $this->person->personTeams()->with([
            'team', 
            'teamRole' => fn($q) => $q->withoutGlobalScopes(),
            'teamRole.roleRelation',
        ]);
    }

    public function headers()
    {
        return [
            _Th('translate.role'),
            _Th('translate.team'),
            _Th('translate.date'),
            _Th('translate.status'),
            _Th()->class('w-8'),
        ];
    }

    public function render($personTeam) {
        return _TableRow(
            _Html($personTeam->teamRole?->roleRelation?->name ?: '-')->class('font-semibold'),
            $personTeam->team->getFullInfoTableElement(),
            _Rows(
                _Html($personTeam->from->format('d/m/Y')),
                _Html($personTeam->to?->format('d/m/Y'))->class('text-gray-400'),
            ),
            $personTeam->teamRole?->statusPill() ?? $personTeam->getStatusPillElement(),
            _Html(),

            _TripleDotsDropdown(
                _Link('translate.delete')->class('py-1 px-3')->selfPost('deleteAsignation', ['team_role_id' => $personTeam->id])->refresh(),
                ($personTeam->teamRole && !$personTeam->teamRole->terminated_at || !$personTeam->to) 
                    ? _Link('translate.terminate')->class('py-1 px-3')->selfPost('terminateRole', ['team_role_id' => $personTeam->id])->refresh()
                    : null,
            ),
        );
    }

    public function terminateRole($personTeamId)
    {
        $teamRole = PersonTeam::findOrFail($personTeamId);
        $teamRole->terminate();
    }

    public function deleteAsignation($personTeamId)
    {
        $teamRole = PersonTeam::findOrFail($personTeamId);
        $teamRole->deleteAsignation();
    }

    public function getAssignRoleModal()
    {
        if (!$this->person->relatedUser) {
            // Here we could open a modal to set a new personTeam without teamRole
            return _CardWhiteP4(_Html('translate.user-not-linked'));
        }

        return new (config('kompo-auth.assign-role-modal-namespace'))([
            'user_id' => $this->person->relatedUser?->id,
        ]);
    }
}