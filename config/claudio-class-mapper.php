<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your AI API settings here. You can specify the provider,
    | API key, and other required parameters.
    |
    */
    'ai_provider' => env('CLAUDIO_AI_PROVIDER', 'openai'),
    
    'api_key' => env('CLAUDIO_API_KEY'),
    
    'model' => env('CLAUDIO_MODEL', 'gpt-4o'),
    
    /*
    |--------------------------------------------------------------------------
    | Class Mapping Settings
    |--------------------------------------------------------------------------
    |
    | Configure how the class mapper works, including paths to scan,
    | exclusions, and caching options.
    |
    */
    'scan_paths' => [
        'app',
    ],
    
    'exclude_paths' => [
        'app/Http/Middleware',
        'app/Exceptions',
    ],
    
    'cache_duration' => 60 * 24, // Cache for 24 hours in minutes
    
    /*
    |--------------------------------------------------------------------------
    | Output Settings
    |--------------------------------------------------------------------------
    |
    | Configure how the class mapper formats and stores its output.
    |
    */
    'storage_path' => storage_path('app/claudio-class-mapper'),
    
    'storage_disk' => 'local',
    
    'max_tokens_per_chunk' => 8000,
];
