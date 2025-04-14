<?php

namespace Cascade\ClaudioClassMapper\Tests\Unit;

use Cascade\ClaudioClassMapper\Facades\ClassMapper as ClassMapperFacade;
use Cascade\ClaudioClassMapper\Tests\Fixtures\TestUser;
use Cascade\ClaudioClassMapper\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Mockery;

class FacadeTest extends TestCase
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
    
    public function testFacadeGeneratesClassMap()
    {
        $result = ClassMapperFacade::generateClassMap([TestUser::class]);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey(TestUser::class, $result);
        
        // Verify file was saved
        $this->assertTrue(File::exists($this->storagePath . '/class-map.json'));
    }
    
    public function testFacadeLoadsClassMap()
    {
        // First generate a class map
        ClassMapperFacade::generateClassMap([TestUser::class]);
        
        // Then load it
        $result = ClassMapperFacade::loadClassMap();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey(TestUser::class, $result);
    }
    
    public function testFacadeQueryWithContext()
    {
        // Mock the underlying class mapper
        $this->mock('claudio-class-mapper', function ($mock) {
            $mock->shouldReceive('queryWithContext')
                ->with('What methods does TestUser have?', [TestUser::class])
                ->once()
                ->andReturn('This is a test response from AI');
        });
        
        // First generate a class map
        ClassMapperFacade::generateClassMap([TestUser::class]);
        
        // Then query with context
        $response = ClassMapperFacade::queryWithContext(
            'What methods does TestUser have?',
            [TestUser::class]
        );
        
        $this->assertEquals('This is a test response from AI', $response);
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