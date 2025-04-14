<?php

namespace PeakFlow\CodeMapper\Tests\Fixtures\Excluded;

class ExcludedClass
{
    public function shouldNotBeIncluded()
    {
        return 'This class should be excluded from the class map';
    }
}
