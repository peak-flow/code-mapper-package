<?php

namespace PeakFlow\CodeMapper\Tests;

use PeakFlow\CodeMapper\CodeMapperServiceProvider;
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
            CodeMapperServiceProvider::class,
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
        
        // Setup code-mapper config
        $app['config']->set('code-mapper.ai_provider', 'openai');
        $app['config']->set('code-mapper.api_key', 'test-api-key');
        $app['config']->set('code-mapper.model', 'gpt-4o');
        $app['config']->set('code-mapper.scan_paths', ['tests/Fixtures']);
        $app['config']->set('code-mapper.exclude_paths', ['tests/Fixtures/Excluded']);
        $app['config']->set('code-mapper.storage_path', __DIR__ . '/storage/code-mapper');
    }
    
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Create storage directory
        $storageDir = __DIR__ . '/storage/code-mapper';
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
    }
}
