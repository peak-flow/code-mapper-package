# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build/Test Commands
- Install dependencies: `composer install`
- Run all tests: `php vendor/bin/phpunit`
- Run single test: `php vendor/bin/phpunit --filter=TestName`
- Run specific test file: `php vendor/bin/phpunit tests/Unit/ClassMapperTest.php`
- Run tests in a specific directory: `php vendor/bin/phpunit tests/Unit`

## Code Style Guidelines
- PSR-4 autoloading standards
- Class names: PascalCase
- Method/function names: camelCase
- Docblocks for all methods/classes with @param, @return, etc.
- Type hints for parameters and return types
- Error handling: Use exception handling with try/catch blocks
- Namespaces: Cascade\ClaudioClassMapper for main code, Cascade\ClaudioClassMapper\Tests for tests
- Imports: Group by type (Laravel, PHP core, custom)
- 4 spaces for indentation, no tabs
- when creating features
