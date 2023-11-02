<?php

namespace Alpa\Tools\Tests\Sucker\Fixtures;

class CoreClass
{
    private $private_core_prop = 'private_core_prop';
    private $private_prop = 'private_core_prop';
    protected $protected_prop = 'protected_core_prop';
    public $public_prop = 'public_core_prop';
    public $public_core_prop = 'public_core_prop';

    private static $static_private_core_prop = 'static_private_core_prop';
    private static $static_private_prop = 'static_private_core_prop';
    protected static $static_protected_prop = 'static_protected_core_prop';
    public static $static_public_prop = 'static_public_core_prop';
    public static $static_public_core_prop = 'static_public_core_prop';

    private function private_core_method()
    {
        return 'private_core_method';
    }

    private function private_method()
    {
        return 'private_core_method';
    }

    private function &testReference(&$value)
    {
        return $value;
    }
    private function &testReturnReference(object $object)
    {
        return $object->prop;
    }
    private static function &static_testReference(&$value)
    {
        return $value;
    }

    private static function static_private_core_method()
    {
        return 'static_private_core_method';
    }

    private static function static_private_method()
    {
        return 'static_private_core_method';
    }
}