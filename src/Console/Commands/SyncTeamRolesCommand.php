<?php

namespace Condoedge\Crm\Console\Commands;

use Condoedge\Crm\Facades\PersonTeamModel;
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
        $terminated = 0;

        PersonTeamModel::pendingTeamRoleSync()
            ->chunkById(100, function ($personTeams) use (&$terminated) {
                $personTeams->each(function ($personTeam) use (&$terminated) {
                    $personTeam->terminate();
                    $terminated++;
                });
            }, 'person_teams.id', 'id');

        $this->info($terminated . ' person team(s) terminated.');
    }
}
