# Code Mapper Package

A Laravel package that allows developers to select specific classes in their applications and use AI API calls to create code maps. These code maps enable sending questions to AI systems using smaller context windows, resulting in more efficient and cost-effective AI interactions.

## Installation

You can install the package via composer:

```bash
composer require peak-flow/code-mapper-package
```

After installing the package, publish the configuration file:

```bash
php artisan vendor:publish --provider="Cascade\ClaudioClassMapper\ClaudioClassMapperServiceProvider" --tag="config"
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

Configure your AI API settings in your `.env` file:

```
CLAUDIO_AI_PROVIDER=openai # or anthropic
CLAUDIO_API_KEY=your-api-key
CLAUDIO_MODEL=gpt-4o # or claude-3-opus-20240229
```

Edit the published configuration file at `config/claudio-class-mapper.php` to customize:

- Scan paths for class discovery
- Excluded paths
- Output and caching settings
- Token limits

## Usage

### Generating Class Maps

Generate a class map of all classes in your configured scan paths:

```bash
php artisan claudio:generate
```

Or specify particular classes:

```bash
php artisan claudio:generate --class=App\\Models\\User --class=App\\Services\\UserService
```

### Managing Class Groups

Create a group of related classes:

```bash
php artisan claudio:groups create auth-system --class=App\\Models\\User --class=App\\Http\\Controllers\\Auth\\LoginController --description="Authentication system classes"
```

List all groups:

```bash
php artisan claudio:groups list
```

Show details of a specific group:

```bash
php artisan claudio:groups show auth-system
```

Update a group:

```bash
php artisan claudio:groups update auth-system --class=App\\Models\\User --class=App\\Http\\Controllers\\Auth\\RegisterController
```

Delete a group:

```bash
php artisan claudio:groups delete auth-system
```

### Querying with Context

Ask questions about specific classes:

```bash
php artisan claudio:query "How does the user registration process work?" --class=App\\Models\\User --class=App\\Http\\Controllers\\Auth\\RegisterController
```

Or use a predefined group:

```bash
php artisan claudio:query "How does the user registration process work?" --group=auth-system
```

### Using in Code

```php
use Cascade\ClaudioClassMapper\Facades\ClassMapper;

// Generate a class map
ClassMapper::generateClassMap(['App\\Models\\User', 'App\\Services\\UserService']);

// Query with context
$answer = ClassMapper::queryWithContext(
    'How does the user authentication flow work?',
    ['App\\Models\\User', 'App\\Http\\Controllers\\Auth\\LoginController']
);
```

## How It Works

1. The package scans your application's classes using PHP's reflection API
2. It extracts class information, methods, properties, and source code
3. When you query the AI, it sends only the relevant classes' context
4. This focused context allows for more efficient use of AI context windows

## Benefits

- Reduced token usage in AI API calls (lower costs)
- More focused AI responses by providing only relevant code context
- Ability to group related classes for common questions
- Command-line and programmatic interfaces for flexibility

## License

This package is open-sourced software licensed under the MIT license.