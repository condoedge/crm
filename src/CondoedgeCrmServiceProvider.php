<?php

namespace Condoedge\Crm;

use Condoedge\Crm\Facades\PersonModel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class CondoedgeCrmServiceProvider extends ServiceProvider
{
    use \Kompo\Routing\Mixins\ExtendsRoutingTrait;

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadHelpers();

        $this->registerPolicies();

        $this->extendRouting(); //otherwise Route::layout doesn't work

        $this->loadJSONTranslationsFrom(__DIR__.'/../resources/lang');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadConfig();

        $this->loadRelationsMorphMap();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'kompo-auth');

        $this->setCommands();
        $this->setCronJobs();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //Best way to load routes. This ensures loading at the very end (after fortifies' routes for ex.)
        $this->booted(function () {
            \Route::middleware('web')->group(__DIR__.'/../routes/web.php');
        });

        $this->app->bind('person-model', function () {
            return new (config('condoedge-crm.person-model-namespace'));
        });

        $this->app->bind('inscription-model', function () {
            return new (config('condoedge-crm.inscription-model-namespace'));
        });
        
        $this->app->bind('event-model', function () {
            return new (config('condoedge-crm.event-model-namespace'));
        });
    }

    protected function loadHelpers()
    {
        $helpersDir = __DIR__.'/Helpers';

        $autoloadedHelpers = collect(\File::allFiles($helpersDir))->map(fn($file) => $file->getRealPath());

        $packageHelpers = [
        ];

        $autoloadedHelpers->concat($packageHelpers)->each(function ($path) {
            if (file_exists($path)) {
                require_once $path;
            }
        });
    }

    protected function registerPolicies()
    {
        $policies = [

        ];

        foreach ($policies as $key => $value) {
            \Gate::policy($key, $value);
        }
    }

    protected function loadConfig()
    {
        $dirs = [
            'condoedge-crm' => __DIR__.'/../config/condoedge-crm.php',            
        ];

        foreach ($dirs as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }

    protected function setCommands()
    {
        $this->commands([
            \Condoedge\Crm\Console\Commands\SyncDiciplinaryActionsCommand::class,
            \Condoedge\Crm\Console\Commands\SyncTeamRolesCommand::class,
        ]);
    }

    protected function setCronJobs()
    {
        $schedule = $this->app->make(Schedule::class);

        $schedule->command('crm:sync-diciplinary-actions-command')->daily();
        $schedule->command('crm:sync-team-roles-command')->daily();
    }
    
    /**
     * Loads a relations morph map.
     */
    protected function loadRelationsMorphMap()
    {
        Relation::morphMap([
            'event' => config('condoedge-crm.event-model-namespace'),
            'person' => PersonModel::getClass(),
        ]);
    }
}
