<?php

namespace PeakFlow\CodeMapper\Tests\Unit;

use PeakFlow\CodeMapper\ClassMapper;
use PeakFlow\CodeMapper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class StorageTest extends TestCase
{
    protected $classMapper;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure test environment
        Config::set('code-mapper.storage_path', storage_path('app/code-mapper'));
        Config::set('code-mapper.storage_disk', 'local');

        // Create class mapper instance with the real application
        $this->classMapper = new ClassMapper($this->app);

        // Clear any existing test data
        Storage::disk('local')->deleteDirectory('code-mapper');
    }

    public function testClassMapSaveAndLoad()
    {
        // Mock data to test saving and loading
        $testData = [
            'App\\TestClass' => [
                'name' => 'App\\TestClass',
                'file_path' => '/path/to/TestClass.php',
                'content' => 'class TestClass {}',
                'methods' => [],
                'properties' => [],
                'namespace' => 'App',
                'short_name' => 'TestClass',
            ]
        ];

        // Use reflection to set and test protected property
        $reflectionClass = new \ReflectionClass(ClassMapper::class);
        $property = $reflectionClass->getProperty('classData');
        $property->setAccessible(true);
        $property->setValue($this->classMapper, $testData);

        // Use reflection to access and test protected method
        $saveMethod = $reflectionClass->getMethod('saveClassMap');
        $saveMethod->setAccessible(true);
        $saveMethod->invoke($this->classMapper);

        // Verify file exists
        $relativePath = str_replace(storage_path('app/'), '', config('code-mapper.storage_path'));
        $mapPath = $relativePath . '/class-map.json';
        $this->assertTrue(Storage::disk('local')->exists($mapPath));

        // Clear the class data and load it back
        $property->setValue($this->classMapper, []);
        $this->assertEquals([], $property->getValue($this->classMapper));

        // Load the data
        $this->classMapper->loadClassMap();

        // Verify the data was loaded correctly
        $loadedData = $property->getValue($this->classMapper);
        $this->assertEquals($testData, $loadedData);
    }
}
