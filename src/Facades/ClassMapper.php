<?php

namespace Cascade\ClaudioClassMapper\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array generateClassMap(array $classNames = [])
 * @method static array loadClassMap()
 * @method static string queryWithContext(string $query, array $classNames)
 * 
 * @see \Cascade\ClaudioClassMapper\ClassMapper
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
        return 'claudio-class-mapper';
    }
}
