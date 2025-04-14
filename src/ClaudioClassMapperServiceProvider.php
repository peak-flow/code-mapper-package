<?php

namespace Cascade\ClaudioClassMapper;

use Illuminate\Support\ServiceProvider;
use Cascade\ClaudioClassMapper\Console\Commands\GenerateClassMapCommand;
use Cascade\ClaudioClassMapper\Console\Commands\QueryWithClassMapCommand;
use Cascade\ClaudioClassMapper\Console\Commands\ManageGroupsCommand;

class ClaudioClassMapperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/claudio-class-mapper.php' => config_path('claudio-class-mapper.php'),
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
            __DIR__ . '/../config/claudio-class-mapper.php', 'claudio-class-mapper'
        );

        $this->app->singleton('claudio-class-mapper', function ($app) {
            return new ClassMapper($app);
        });
        
        $this->app->singleton(GroupManager::class, function ($app) {
            return new GroupManager();
        });
    }
}
