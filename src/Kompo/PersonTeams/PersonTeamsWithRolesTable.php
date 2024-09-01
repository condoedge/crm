<?php

namespace Condoedge\Crm\Kompo\PersonTeams;

use Condoedge\Crm\Facades\PersonModel;
use Condoedge\Crm\Models\PersonTeam;
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
            _Toggle('translate.show-inactive')->name('show_all', false)->filter()
                ->class('[&>.vlFormLabel]:w-max !mb-0'),
            _Dropdown('permissions.actions')->button()
                ->submenu(
                    _Link('permissions.assign-role')->class('py-1 px-3')->selfGet('getAssignRoleModal')->inModal(),
                ),
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
                _DeleteLink('permissions.delete')->class('py-1 px-3 text-danger')->selfDelete('deleteAsignation', ['team_role_id' => $personTeam->id])->browse(),
                ($personTeam->teamRole && !$personTeam->teamRole->terminated_at || !$personTeam->to) 
                    ? _Link('permissions.terminate')->class('py-1 px-3')->selfPost('terminateRole', ['team_role_id' => $personTeam->id])->browse()
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
            return _CardWhiteP4(_Html('permissions.user-not-linked'));
        }

        return new (config('kompo-auth.assign-role-modal-namespace'))([
            'user_id' => $this->person->relatedUser?->id,
        ]);
    }
}