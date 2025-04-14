<?php

namespace PeakFlow\CodeMapper;

use Illuminate\Support\ServiceProvider;
use PeakFlow\CodeMapper\Console\Commands\GenerateClassMapCommand;
use PeakFlow\CodeMapper\Console\Commands\QueryWithClassMapCommand;
use PeakFlow\CodeMapper\Console\Commands\ManageGroupsCommand;

class CodeMapperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/code-mapper.php' => config_path('code-mapper.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateClassMapCommand::class,
                QueryWithClassMapCommand::class,
                ManageGroupsCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/code-mapper.php', 'code-mapper'
        );

        $this->app->singleton('code-mapper', function ($app) {
            return new ClassMapper($app);
        });
        
        $this->app->singleton(GroupManager::class, function ($app) {
            return new GroupManager();
        });
    }
}
