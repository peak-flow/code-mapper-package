<?php

namespace Cascade\ClaudioClassMapper\Tests;

use Cascade\ClaudioClassMapper\ClaudioClassMapperServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ClaudioClassMapperServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        
        // Setup claudio-class-mapper config
        $app['config']->set('claudio-class-mapper.ai_provider', 'openai');
        $app['config']->set('claudio-class-mapper.api_key', 'test-api-key');
        $app['config']->set('claudio-class-mapper.model', 'gpt-4o');
        $app['config']->set('claudio-class-mapper.scan_paths', ['tests/Fixtures']);
        $app['config']->set('claudio-class-mapper.exclude_paths', ['tests/Fixtures/Excluded']);
        $app['config']->set('claudio-class-mapper.storage_path', __DIR__ . '/storage/claudio-class-mapper');
    }
    
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Create storage directory
        $storageDir = __DIR__ . '/storage/claudio-class-mapper';
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
    }
}
