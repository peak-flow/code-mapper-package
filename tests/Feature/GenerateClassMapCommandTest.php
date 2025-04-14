<?php

namespace Cascade\ClaudioClassMapper\Tests\Feature;

use Cascade\ClaudioClassMapper\Tests\Fixtures\TestService;
use Cascade\ClaudioClassMapper\Tests\Fixtures\TestUser;
use Cascade\ClaudioClassMapper\Tests\TestCase;
use Illuminate\Support\Facades\File;

class GenerateClassMapCommandTest extends TestCase
{
    protected $storagePath;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->storagePath = config('claudio-class-mapper.storage_path');
        
        // Delete any existing class map
        if (File::exists($this->storagePath . '/class-map.json')) {
            File::delete($this->storagePath . '/class-map.json');
        }
    }
    
    public function testGenerateClassMapCommand()
    {
        $this->artisan('claudio:generate-map')
            ->expectsOutput('Generating class map...')
            ->expectsOutput('Class map generated successfully!')
            ->assertExitCode(0);
        
        $this->assertTrue(File::exists($this->storagePath . '/class-map.json'));
        
        // Check content of class map
        $classMap = json_decode(File::get($this->storagePath . '/class-map.json'), true);
        $this->assertArrayHasKey(TestUser::class, $classMap);
        $this->assertArrayHasKey(TestService::class, $classMap);
    }
    
    public function testGenerateClassMapWithSpecificClasses()
    {
        $this->artisan('claudio:generate-map', [
            '--class' => [TestUser::class],
        ])
            ->expectsOutput('Generating class map...')
            ->expectsOutput('Class map generated successfully!')
            ->expectsOutput('Total classes mapped: 1')
            ->assertExitCode(0);
        
        // Check content of class map
        $classMap = json_decode(File::get($this->storagePath . '/class-map.json'), true);
        $this->assertCount(1, $classMap);
        $this->assertArrayHasKey(TestUser::class, $classMap);
    }
    
    public function testGenerateClassMapWithForceOption()
    {
        // First generate a map
        File::put(
            $this->storagePath . '/class-map.json',
            json_encode([TestUser::class => ['name' => TestUser::class]])
        );
        
        // Then force regenerate it
        $this->artisan('claudio:generate-map', [
            '--force' => true,
        ])
            ->expectsOutput('Generating class map...')
            ->expectsOutput('Class map generated successfully!')
            ->assertExitCode(0);
        
        // Check that the file has been updated
        $classMap = json_decode(File::get($this->storagePath . '/class-map.json'), true);
        $this->assertArrayHasKey(TestUser::class, $classMap);
        $this->assertArrayHasKey(TestService::class, $classMap);
    }
    
    public function testGenerateClassMapWithConfirmation()
    {
        // First generate a map
        File::put(
            $this->storagePath . '/class-map.json',
            json_encode([TestUser::class => ['name' => TestUser::class]])
        );
        
        // Then try to regenerate it with confirmation
        $this->artisan('claudio:generate-map')
            ->expectsOutput('Generating class map...')
            ->expectsQuestion('A class map already exists. Do you want to regenerate it?', true)
            ->expectsOutput('Class map generated successfully!')
            ->assertExitCode(0);
        
        // Check that the file has been updated
        $classMap = json_decode(File::get($this->storagePath . '/class-map.json'), true);
        $this->assertArrayHasKey(TestUser::class, $classMap);
        $this->assertArrayHasKey(TestService::class, $classMap);
    }
    
    public function testGenerateClassMapWithConfirmationCancelled()
    {
        // First generate a map
        File::put(
            $this->storagePath . '/class-map.json',
            json_encode([TestUser::class => ['name' => TestUser::class]])
        );
        
        // Then try to regenerate it with confirmation cancelled
        $this->artisan('claudio:generate-map')
            ->expectsOutput('Generating class map...')
            ->expectsQuestion('A class map already exists. Do you want to regenerate it?', false)
            ->expectsOutput('Operation cancelled.')
            ->assertExitCode(0);
    }
    
    protected function tearDown(): void
    {
        if (File::exists($this->storagePath . '/class-map.json')) {
            File::delete($this->storagePath . '/class-map.json');
        }
        
        parent::tearDown();
    }
}