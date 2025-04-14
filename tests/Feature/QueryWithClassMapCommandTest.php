<?php

namespace PeakFlow\CodeMapper\Tests\Feature;

use PeakFlow\CodeMapper\ClassMapper;
use PeakFlow\CodeMapper\GroupManager;
use PeakFlow\CodeMapper\Tests\Fixtures\TestUser;
use PeakFlow\CodeMapper\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Mockery;

class QueryWithClassMapCommandTest extends TestCase
{
    protected $storagePath;
    protected $mockClassMapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storagePath = config('code-mapper.storage_path');

        // Delete any existing class map
        if (File::exists($this->storagePath . '/class-map.json')) {
            File::delete($this->storagePath . '/class-map.json');
        }

        // Create a mock ClassMapper
        $this->mockClassMapper = Mockery::mock(ClassMapper::class, [$this->app])->makePartial();
        $this->mockClassMapper->shouldReceive('queryWithContext')
            ->andReturn('This is a test response from AI');

        $this->app->instance(ClassMapper::class, $this->mockClassMapper);

        // Create the class map
        $this->mockClassMapper->generateClassMap([TestUser::class]);

        // Run migrations
        $this->artisan('migrate:fresh');
    }

    public function testQueryWithClasses()
    {
        $this->artisan('code:query', [
            'query' => 'What methods does the TestUser class have?',
            '--class' => [TestUser::class],
        ])
            ->expectsOutput('Querying AI with context...')
            ->expectsOutput("\nThis is a test response from AI")
            ->assertExitCode(0);
    }

    public function testQueryWithoutClassesOrGroup()
    {
        $this->artisan('code:query', [
            'query' => 'What methods does the TestUser class have?',
        ])
            ->expectsOutput('You must specify either classes or a group to include in the context.')
            ->assertExitCode(1);
    }

    public function testQueryWithGroup()
    {
        // Create a group
        $groupManager = app(GroupManager::class);
        $groupManager->createGroup('test-group', [TestUser::class], 'Test group');

        $this->artisan('code:query', [
            'query' => 'What methods does the TestUser class have?',
            '--group' => 'test-group',
        ])
            ->expectsOutput('Querying AI with context...')
            ->expectsOutput("\nThis is a test response from AI")
            ->assertExitCode(0);
    }

    public function testQueryWithNonExistentGroup()
    {
        $this->artisan('code:query', [
            'query' => 'What methods does the TestUser class have?',
            '--group' => 'non-existent-group',
        ])
            ->expectsOutput("Group 'non-existent-group' not found.")
            ->assertExitCode(1);
    }

    public function testQueryWithEmptyGroup()
    {
        // Create an empty group
        $groupManager = app(GroupManager::class);
        $groupManager->createGroup('empty-group', [], 'Empty group');

        $this->artisan('code:query', [
            'query' => 'What methods does the TestUser class have?',
            '--group' => 'empty-group',
        ])
            ->expectsOutput("Group 'empty-group' has no classes defined.")
            ->assertExitCode(1);
    }

    public function testQueryWithError()
    {
        // Create a mock that throws an exception
        $errorMock = Mockery::mock(ClassMapper::class, [$this->app])->makePartial();
        $errorMock->shouldReceive('queryWithContext')
            ->andThrow(new \Exception('Test error'));

        $this->app->instance(ClassMapper::class, $errorMock);

        $this->artisan('code:query', [
            'query' => 'What methods does the TestUser class have?',
            '--class' => [TestUser::class],
        ])
            ->expectsOutput('Querying AI with context...')
            ->expectsOutput('Error querying AI: Test error')
            ->assertExitCode(1);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->storagePath . '/class-map.json')) {
            File::delete($this->storagePath . '/class-map.json');
        }

        Mockery::close();

        parent::tearDown();
    }
}
