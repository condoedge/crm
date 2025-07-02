<?php

namespace Condoedge\Crm\Console\Commands;

use Condoedge\Crm\Models\PersonTeam;
use Condoedge\Crm\Models\PersonTeamStatusEnum;
use Illuminate\Console\Command;

class SyncTeamRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:sync-team-roles-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        PersonTeam::whereRaw('DATE(person_teams.to) <= CURDATE()')
            ->withTrashed()
            ->selectRaw('team_role_id')
            ->whereHas('teamRole') // If has teamRole it means it is not terminated or deleted
            ->chunk(100, function ($personTeams) {
                $personTeams->each(function ($personTeam) {
                    $personTeam->teamRole()->withoutGlobalScopes()->first()?->terminate();

                    if (!$personTeam->deleted_at) {
                        $personTeam->status = PersonTeamStatusEnum::TERMINATED;
                        $personTeam->deleted_at = now();
                        $personTeam->save();
                    }
                });
            });
    }
}
