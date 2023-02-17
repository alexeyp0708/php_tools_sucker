<?php

namespace Alpa\Tools\Tests\Sucker;

use Alpa\Tools\Sucker\TSucker;
use PHPUnit\Framework\TestCase;

class TSuckerTest extends TestCase
{
    public function test_suckerTrait()
    {
        $inst = new class () {
            use TSucker;

            private $prop = 'hello';
        };
        static::assertTrue($inst('prop') === 'hello');
        static::assertTrue($inst('prop', 'isset'));
        $inst('prop', 'set', 'bay');
        static::assertTrue($inst('prop', 'get') === 'bay');
    }
}