<?php

namespace Cascade\ClaudioClassMapper\Tests\Unit;

use Cascade\ClaudioClassMapper\ClassMapper;
use Cascade\ClaudioClassMapper\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;

class AnthropicApiTest extends TestCase
{
    protected $classMapper;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up config
        Config::set('claudio-class-mapper.ai_provider', 'anthropic');
        Config::set('claudio-class-mapper.api_key', 'test-api-key');
        Config::set('claudio-class-mapper.model', 'claude-3-opus-20240229');
        
        // Create class mapper instance with the real application
        $this->classMapper = new ClassMapper($this->app);
    }

    public function testAnthropicApiCall()
    {
        // Create a mock response
        $mockResponse = [
            'id' => 'msg_01ABCDEFG',
            'type' => 'message',
            'role' => 'assistant',
            'content' => 'This is a test response from Claude.',
            'model' => 'claude-3-opus-20240229',
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 100,
                'output_tokens' => 50
            ]
        ];

        // Create a mock handler
        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        // Use reflection to access and test protected method
        $reflectionClass = new \ReflectionClass(ClassMapper::class);
        $method = $reflectionClass->getMethod('callAnthropic');
        $method->setAccessible(true);

        // Call the method with the mock client
        $result = $method->invokeArgs($this->classMapper, [
            $mockClient, 
            'test-api-key', 
            'claude-3-opus-20240229', 
            'Test query', 
            'Test context'
        ]);

        // Assert the result matches the mock response
        $this->assertEquals('This is a test response from Claude.', $result);
    }

    public function testAnthropicApiWithLegacyResponse()
    {
        // Create a mock response in the legacy format
        $mockResponse = [
            'id' => 'msg_01ABCDEFG',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'This is a legacy format response from Claude.'
                ]
            ],
            'model' => 'claude-3-opus-20240229',
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 100,
                'output_tokens' => 50
            ]
        ];

        // Create a mock handler
        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        // Use reflection to access and test protected method
        $reflectionClass = new \ReflectionClass(ClassMapper::class);
        $method = $reflectionClass->getMethod('callAnthropic');
        $method->setAccessible(true);

        // Call the method with the mock client
        $result = $method->invokeArgs($this->classMapper, [
            $mockClient, 
            'test-api-key', 
            'claude-3-opus-20240229', 
            'Test query', 
            'Test context'
        ]);

        // Assert the result matches the mock response
        $this->assertEquals('This is a legacy format response from Claude.', $result);
    }
}