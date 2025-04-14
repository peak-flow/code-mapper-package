<?php

namespace Cascade\ClaudioClassMapper;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ReflectionClass;

class ClassMapper
{
    protected $app;
    protected $classData = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Scan the application for classes and generate class maps
     *
     * @return array
     */
    public function generateClassMap(array $classNames = []): array
    {
        $scanPaths = config('claudio-class-mapper.scan_paths');
        $excludePaths = config('claudio-class-mapper.exclude_paths');
        
        if (empty($classNames)) {
            // Discover all classes in scan paths
            $classNames = $this->discoverClasses($scanPaths, $excludePaths);
        }
        
        foreach ($classNames as $className) {
            try {
                $reflection = new ReflectionClass($className);
                $filePath = $reflection->getFileName();
                
                // Skip if the file doesn't exist or is in excluded paths
                if (!$filePath || $this->isExcluded($filePath, $excludePaths)) {
                    continue;
                }
                
                $fileContent = File::get($filePath);
                
                $this->classData[$className] = [
                    'name' => $className,
                    'file_path' => $filePath,
                    'content' => $fileContent,
                    'methods' => $this->extractMethods($reflection),
                    'properties' => $this->extractProperties($reflection),
                    'namespace' => $reflection->getNamespaceName(),
                    'short_name' => $reflection->getShortName(),
                ];
            } catch (\Exception $e) {
                // Log error but continue processing other classes
                logger()->error("Error processing class {$className}: " . $e->getMessage());
            }
        }
        
        $this->saveClassMap();
        
        return $this->classData;
    }
    
    /**
     * Discover all classes in the given paths
     *
     * @param array $paths
     * @param array $excludePaths
     * @return array
     */
    protected function discoverClasses(array $paths, array $excludePaths): array
    {
        $classes = [];

        foreach ($paths as $path) {
            $files = File::allFiles(base_path($path));
            
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                
                if ($this->isExcluded($file->getPathname(), $excludePaths)) {
                    continue;
                }
                
                $className = $this->getClassNameFromFile($file->getPathname());
                if ($className) {
                    $classes[] = $className;
                }
            }
        }

        return $classes;
    }
    
    /**
     * Check if a file is in excluded paths
     *
     * @param string $filePath
     * @param array $excludePaths
     * @return bool
     */
    protected function isExcluded(string $filePath, array $excludePaths): bool
    {
        $relativePath = Str::replaceFirst(base_path(), '', $filePath);
        $relativePath = ltrim($relativePath, '/');
        
        foreach ($excludePaths as $excludePath) {
            if (Str::startsWith($relativePath, $excludePath)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get the fully qualified class name from a file
     *
     * @param string $filePath
     * @return string|null
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $content = File::get($filePath);
        $tokens = token_get_all($content);
        $namespace = '';
        $className = null;
        
        foreach ($tokens as $index => $token) {
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                for ($i = $index + 1; $i < count($tokens); $i++) {
                    if (is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
                        $namespace .= '\\' . $tokens[$i][1];
                    } else if ($tokens[$i] === '{' || $tokens[$i] === ';') {
                        break;
                    }
                }
            }
            
            if (is_array($token) && $token[0] == T_CLASS) {
                for ($i = $index + 1; $i < count($tokens); $i++) {
                    if (is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
                        $className = $tokens[$i][1];
                        break;
                    }
                }
            }
        }
        
        if ($className !== null) {
            return ltrim($namespace, '\\') . '\\' . $className;
        }
        
        return null;
    }
    
    /**
     * Extract methods information from a class
     *
     * @param ReflectionClass $reflection
     * @return array
     */
    protected function extractMethods(ReflectionClass $reflection): array
    {
        $methods = [];
        
        foreach ($reflection->getMethods() as $method) {
            // Skip methods from parent classes
            if ($method->getDeclaringClass()->getName() !== $reflection->getName()) {
                continue;
            }
            
            $methods[$method->getName()] = [
                'name' => $method->getName(),
                'visibility' => $this->getVisibility($method),
                'static' => $method->isStatic(),
                'parameters' => $this->extractParameters($method),
                'doc_comment' => $method->getDocComment() ?: '',
            ];
        }
        
        return $methods;
    }
    
    /**
     * Extract property information from a class
     *
     * @param ReflectionClass $reflection
     * @return array
     */
    protected function extractProperties(ReflectionClass $reflection): array
    {
        $properties = [];
        
        foreach ($reflection->getProperties() as $property) {
            // Skip properties from parent classes
            if ($property->getDeclaringClass()->getName() !== $reflection->getName()) {
                continue;
            }
            
            $properties[$property->getName()] = [
                'name' => $property->getName(),
                'visibility' => $this->getVisibility($property),
                'static' => $property->isStatic(),
                'doc_comment' => $property->getDocComment() ?: '',
            ];
        }
        
        return $properties;
    }
    
    /**
     * Extract parameter information from a method
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    protected function extractParameters(\ReflectionMethod $method): array
    {
        $parameters = [];
        
        foreach ($method->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = [
                'name' => $parameter->getName(),
                'type' => $parameter->hasType() ? $parameter->getType()->getName() : null,
                'default_value' => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                'is_optional' => $parameter->isOptional(),
            ];
        }
        
        return $parameters;
    }
    
    /**
     * Get the visibility of a method or property
     *
     * @param \ReflectionMethod|\ReflectionProperty $reflection
     * @return string
     */
    protected function getVisibility($reflection): string
    {
        if ($reflection->isPublic()) {
            return 'public';
        }
        
        if ($reflection->isProtected()) {
            return 'protected';
        }
        
        return 'private';
    }
    
    /**
     * Save the class map to storage
     *
     * @return void
     */
    protected function saveClassMap(): void
    {
        $storagePath = config('claudio-class-mapper.storage_path');
        
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }
        
        $mapPath = $storagePath . '/class-map.json';
        File::put($mapPath, json_encode($this->classData, JSON_PRETTY_PRINT));
    }
    
    /**
     * Load the class map from storage
     *
     * @return array
     */
    public function loadClassMap(): array
    {
        $storagePath = config('claudio-class-mapper.storage_path');
        $mapPath = $storagePath . '/class-map.json';
        
        if (File::exists($mapPath)) {
            $this->classData = json_decode(File::get($mapPath), true);
        }
        
        return $this->classData;
    }
    
    /**
     * Query the AI with class context
     *
     * @param string $query
     * @param array $classNames
     * @return string
     */
    public function queryWithContext(string $query, array $classNames): string
    {
        // Make sure we have the class map loaded
        if (empty($this->classData)) {
            $this->loadClassMap();
        }
        
        // Filter to only requested classes
        $context = [];
        foreach ($classNames as $className) {
            if (isset($this->classData[$className])) {
                $context[$className] = $this->classData[$className];
            }
        }
        
        // Generate context for AI
        $aiContext = $this->prepareAiContext($context);
        
        // Call AI API with context and query
        return $this->callAiApi($query, $aiContext);
    }
    
    /**
     * Prepare context for AI
     *
     * @param array $context
     * @return string
     */
    protected function prepareAiContext(array $context): string
    {
        $aiContext = "";
        
        foreach ($context as $className => $classData) {
            $aiContext .= "\n\n# Class: {$className}\n\n";
            $aiContext .= "```php\n{$classData['content']}\n```\n";
        }
        
        return $aiContext;
    }
    
    /**
     * Call AI API with context and query
     *
     * @param string $query
     * @param string $context
     * @return string
     */
    protected function callAiApi(string $query, string $context): string
    {
        $apiKey = config('claudio-class-mapper.api_key');
        $provider = config('claudio-class-mapper.ai_provider');
        $model = config('claudio-class-mapper.model');
        
        $client = new \GuzzleHttp\Client();
        
        if ($provider === 'openai') {
            return $this->callOpenAi($client, $apiKey, $model, $query, $context);
        } elseif ($provider === 'anthropic') {
            return $this->callAnthropic($client, $apiKey, $model, $query, $context);
        }
        
        throw new \Exception("Unsupported AI provider: {$provider}");
    }
    
    /**
     * Call OpenAI API
     *
     * @param \GuzzleHttp\Client $client
     * @param string $apiKey
     * @param string $model
     * @param string $query
     * @param string $context
     * @return string
     */
    protected function callOpenAi($client, $apiKey, $model, $query, $context): string
    {
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant that specializes in PHP and Laravel. ' . 
                                    'You help developers understand their code. ' . 
                                    'Refer only to the provided code context to answer questions.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Code context:\n{$context}\n\nQuestion: {$query}"
                    ]
                ],
                'temperature' => 0.5,
            ],
        ]);
        
        $result = json_decode($response->getBody()->getContents(), true);
        return $result['choices'][0]['message']['content'] ?? 'No response from AI';
    }
    
    /**
     * Call Anthropic API
     *
     * @param \GuzzleHttp\Client $client
     * @param string $apiKey
     * @param string $model
     * @param string $query
     * @param string $context
     * @return string
     */
    protected function callAnthropic($client, $apiKey, $model, $query, $context): string
    {
        $response = $client->post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'system' => 'You are a helpful assistant that specializes in PHP and Laravel. ' . 
                           'You help developers understand their code. ' . 
                           'Refer only to the provided code context to answer questions.',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Code context:\n{$context}\n\nQuestion: {$query}"
                    ]
                ],
                'temperature' => 0.5,
                'max_tokens' => 1000,
            ],
        ]);
        
        $result = json_decode($response->getBody()->getContents(), true);
        return $result['content'][0]['text'] ?? 'No response from AI';
    }
}
