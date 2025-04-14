<?php

namespace Cascade\ClaudioClassMapper\Tests\Fixtures\Excluded;

class ExcludedClass
{
    public function shouldNotBeIncluded()
    {
        return 'This class should be excluded from the class map';
    }
}
