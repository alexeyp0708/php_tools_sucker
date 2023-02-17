<?php

namespace Alpa\Tools\Tests\Sucker\Fixtures;

class MyClass
{
    use MyTrait {
        method as private private_method;
    }

    private static function method4()
    {
        return 'hello';
    }

    private function method3()
    {
        return 'hello';
    }
}