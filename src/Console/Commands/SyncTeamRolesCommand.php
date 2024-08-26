<?php

namespace Condoedge\Crm\Console\Commands;

use Condoedge\Crm\Models\PersonTeam;
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
        $finishedPersonTeams = PersonTeam::whereRaw('DATE(to) = CURDATE()')->selectRaw('team_role_id')->get();

        $finishedPersonTeams->each(function ($personTeam) {
            $personTeam->teamRole?->terminate();
        });
    }
}
