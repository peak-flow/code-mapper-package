<?php

namespace Cascade\ClaudioClassMapper\Tests\Unit;

use Cascade\ClaudioClassMapper\ClassMapper;
use Cascade\ClaudioClassMapper\Console\Commands\GenerateClassMapCommand;
use Cascade\ClaudioClassMapper\Console\Commands\ManageGroupsCommand;
use Cascade\ClaudioClassMapper\Console\Commands\QueryWithClassMapCommand;
use Cascade\ClaudioClassMapper\GroupManager;
use Cascade\ClaudioClassMapper\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function testClassMapperIsBound()
    {
        $this->assertTrue($this->app->bound('claudio-class-mapper'));
        $this->assertInstanceOf(ClassMapper::class, $this->app->make('claudio-class-mapper'));
    }
    
    public function testGroupManagerIsBound()
    {
        $this->assertTrue($this->app->bound(GroupManager::class));
        $this->assertInstanceOf(GroupManager::class, $this->app->make(GroupManager::class));
    }
    
    public function testConfigIsMerged()
    {
        $this->assertEquals('openai', config('claudio-class-mapper.ai_provider'));
        $this->assertEquals('test-api-key', config('claudio-class-mapper.api_key'));
        $this->assertEquals('gpt-4o', config('claudio-class-mapper.model'));
    }
    
    public function testCommandsAreRegistered()
    {
        $commands = $this->app->make('Illuminate\Contracts\Console\Kernel')->all();
        
        $this->assertArrayHasKey('claudio:generate-map', $commands);
        $this->assertArrayHasKey('claudio:query', $commands);
        $this->assertArrayHasKey('claudio:groups', $commands);
        
        $this->assertInstanceOf(GenerateClassMapCommand::class, $commands['claudio:generate-map']);
        $this->assertInstanceOf(QueryWithClassMapCommand::class, $commands['claudio:query']);
        $this->assertInstanceOf(ManageGroupsCommand::class, $commands['claudio:groups']);
    }
    
    public function testMigrationsAreLoaded()
    {
        $this->artisan('migrate');
        
        $this->assertTrue($this->app->make('Illuminate\Database\Schema\Builder')->hasTable('class_maps'));
        $this->assertTrue($this->app->make('Illuminate\Database\Schema\Builder')->hasTable('class_map_groups'));
    }
}