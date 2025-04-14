<?php

namespace PeakFlow\CodeMapper\Tests\Unit;

use PeakFlow\CodeMapper\ClassMapper;
use PeakFlow\CodeMapper\Console\Commands\GenerateClassMapCommand;
use PeakFlow\CodeMapper\Console\Commands\ManageGroupsCommand;
use PeakFlow\CodeMapper\Console\Commands\QueryWithClassMapCommand;
use PeakFlow\CodeMapper\GroupManager;
use PeakFlow\CodeMapper\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function testClassMapperIsBound()
    {
        $this->assertTrue($this->app->bound('code-mapper'));
        $this->assertInstanceOf(ClassMapper::class, $this->app->make('code-mapper'));
    }

    public function testGroupManagerIsBound()
    {
        $this->assertTrue($this->app->bound(GroupManager::class));
        $this->assertInstanceOf(GroupManager::class, $this->app->make(GroupManager::class));
    }

    public function testConfigIsMerged()
    {
        $this->assertEquals('openai', config('code-mapper.ai_provider'));
        $this->assertEquals('test-api-key', config('code-mapper.api_key'));
        $this->assertEquals('gpt-4o', config('code-mapper.model'));
    }

    public function testCommandsAreRegistered()
    {
        $commands = $this->app->make('Illuminate\Contracts\Console\Kernel')->all();

        $this->assertArrayHasKey('code:generate', $commands);
        $this->assertArrayHasKey('code:query', $commands);
        $this->assertArrayHasKey('code:groups', $commands);

        $this->assertInstanceOf(GenerateClassMapCommand::class, $commands['code:generate']);
        $this->assertInstanceOf(QueryWithClassMapCommand::class, $commands['code:query']);
        $this->assertInstanceOf(ManageGroupsCommand::class, $commands['code:groups']);
    }

    public function testMigrationsAreLoaded()
    {
        $this->artisan('migrate');

        $this->assertTrue($this->app->make('Illuminate\Database\Schema\Builder')->hasTable('class_maps'));
        $this->assertTrue($this->app->make('Illuminate\Database\Schema\Builder')->hasTable('class_map_groups'));
    }
}
