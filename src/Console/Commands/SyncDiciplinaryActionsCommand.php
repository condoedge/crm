<?php

namespace Condoedge\Crm\Console\Commands;

use Condoedge\Crm\Models\DiciplinaryAction;
use Illuminate\Console\Command;

class SyncDiciplinaryActionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:sync-diciplinary-actions-command';

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
        $startedDiciplinaryActions = DiciplinaryAction::whereRaw('DATE(action_from) = CURDATE()')->selectRaw('person_id, action_type')->get();

        $startedDiciplinaryActions->each(function ($diciplinaryAction) {
            $diciplinaryAction->action_type->startedAction($diciplinaryAction);
        });

        $finishedDiciplinaryActions = DiciplinaryAction::whereRaw('DATE(action_to) = CURDATE()')->selectRaw('person_id, action_type')->get();

        $finishedDiciplinaryActions->each(function ($diciplinaryAction) {
            $diciplinaryAction->action_type->finishedAction($diciplinaryAction);
        });
    }
}
