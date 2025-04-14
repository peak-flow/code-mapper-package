<?php

namespace Cascade\ClaudioClassMapper\Tests\Unit;

use Cascade\ClaudioClassMapper\ClassMapper;
use Cascade\ClaudioClassMapper\Tests\Fixtures\TestService;
use Cascade\ClaudioClassMapper\Tests\Fixtures\TestUser;
use Cascade\ClaudioClassMapper\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\File;
use Mockery;
use ReflectionClass;

class ClassMapperTest extends TestCase
{
    protected $classMapper;
    protected $storagePath;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->classMapper = app(ClassMapper::class);
        $this->storagePath = config('claudio-class-mapper.storage_path');
        
        // Delete any existing class map
        if (File::exists($this->storagePath . '/class-map.json')) {
            File::delete($this->storagePath . '/class-map.json');
        }
    }
    
    public function testGenerateClassMap()
    {
        $result = $this->classMapper->generateClassMap([
            TestUser::class,
            TestService::class,
        ]);
        
        $this->assertCount(2, $result);
        $this->assertArrayHasKey(TestUser::class, $result);
        $this->assertArrayHasKey(TestService::class, $result);
        
        $userClass = $result[TestUser::class];
        $this->assertEquals(TestUser::class, $userClass['name']);
        $this->assertArrayHasKey('methods', $userClass);
        $this->assertArrayHasKey('properties', $userClass);
        
        // Check that some methods were extracted
        $this->assertArrayHasKey('getId', $userClass['methods']);
        $this->assertArrayHasKey('getName', $userClass['methods']);
        $this->assertArrayHasKey('setName', $userClass['methods']);
        
        // Check that properties were extracted
        $this->assertArrayHasKey('id', $userClass['properties']);
        $this->assertArrayHasKey('name', $userClass['properties']);
        $this->assertArrayHasKey('email', $userClass['properties']);
        
        // Verify file was saved
        $this->assertTrue(File::exists($this->storagePath . '/class-map.json'));
    }
    
    public function testDiscoverClasses()
    {
        $result = $this->classMapper->generateClassMap();
        
        // Should find our test classes but exclude the excluded one
        $this->assertArrayHasKey(TestUser::class, $result);
        $this->assertArrayHasKey(TestService::class, $result);
        
        // Excluded class should not be included
        $this->assertArrayNotHasKey('Cascade\\ClaudioClassMapper\\Tests\\Fixtures\\Excluded\\ExcludedClass', $result);
    }
    
    public function testLoadClassMap()
    {
        // First generate a class map
        $this->classMapper->generateClassMap([TestUser::class]);
        
        // Clear the internal class data
        $reflection = new ReflectionClass($this->classMapper);
        $property = $reflection->getProperty('classData');
        $property->setAccessible(true);
        $property->setValue($this->classMapper, []);
        
        // Load the class map
        $result = $this->classMapper->loadClassMap();
        
        $this->assertCount(1, $result);
        $this->assertArrayHasKey(TestUser::class, $result);
    }
    
    public function testQueryWithContextUsingOpenAI()
    {
        // Mock GuzzleHttp client with a predefined response
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'This is a test response from OpenAI'
                        ]
                    ]
                ]
            ]))
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        // Mock the ClassMapper to use our mock client
        $mockClassMapper = Mockery::mock(ClassMapper::class, [$this->app])->makePartial();
        $mockClassMapper->shouldReceive('callOpenAi')
            ->andReturn('This is a test response from OpenAI');
        
        $this->app->instance(ClassMapper::class, $mockClassMapper);
        
        // First generate a class map
        $mockClassMapper->generateClassMap([TestUser::class]);
        
        // Query with context
        $response = $mockClassMapper->queryWithContext(
            'What methods does the TestUser class have?',
            [TestUser::class]
        );
        
        $this->assertEquals('This is a test response from OpenAI', $response);
    }
    
    public function testQueryWithContextUsingAnthropic()
    {
        // Change the provider to anthropic
        config(['claudio-class-mapper.ai_provider' => 'anthropic']);
        
        // Mock GuzzleHttp client with a predefined response
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'content' => [
                    [
                        'text' => 'This is a test response from Anthropic'
                    ]
                ]
            ]))
        ]);
        
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        // Mock the ClassMapper to use our mock client
        $mockClassMapper = Mockery::mock(ClassMapper::class, [$this->app])->makePartial();
        $mockClassMapper->shouldReceive('callAnthropic')
            ->andReturn('This is a test response from Anthropic');
        
        $this->app->instance(ClassMapper::class, $mockClassMapper);
        
        // First generate a class map
        $mockClassMapper->generateClassMap([TestUser::class]);
        
        // Query with context
        $response = $mockClassMapper->queryWithContext(
            'What methods does the TestUser class have?',
            [TestUser::class]
        );
        
        $this->assertEquals('This is a test response from Anthropic', $response);
    }
    
    public function testQueryWithContextUsingInvalidProvider()
    {
        // Change the provider to an invalid one
        config(['claudio-class-mapper.ai_provider' => 'invalid']);
        
        // First generate a class map
        $this->classMapper->generateClassMap([TestUser::class]);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported AI provider: invalid');
        
        // Query with context
        $this->classMapper->queryWithContext(
            'What methods does the TestUser class have?',
            [TestUser::class]
        );
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