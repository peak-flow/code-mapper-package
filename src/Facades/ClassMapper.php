<?php

namespace PeakFlow\CodeMapper\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array generateClassMap(array $classNames = [])
 * @method static array loadClassMap()
 * @method static string queryWithContext(string $query, array $classNames)
 * 
 * @see \PeakFlow\CodeMapper\ClassMapper
 */
class ClassMapper extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'code-mapper';
    }
}
