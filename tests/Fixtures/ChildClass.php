<?php

namespace Alpa\Tools\Tests\Sucker\Fixtures;

class ChildClass extends CoreClass
{
    private $private_child_prop = 'private_child_prop';
    private $private_prop = 'private_child_prop';
    public $public_prop = 'public_child_prop';
    protected $protected_prop = 'protected_child_prop';
    public $public_child_prop = 'public_child_prop';

    private static $static_private_child_prop = 'static_private_child_prop';
    private static $static_private_prop = 'static_private_child_prop';
    public static $static_public_prop = 'static_public_child_prop';
    protected static $static_protected_prop = 'static_protected_child_prop';
    public static $static_public_child_prop = 'static_public_child_prop';
    
    private function private_child_method()
    {
        return 'private_child_method';
    }

    private function private_method()
    {
        return 'private_child_method';
    }

    protected function protected_method()
    {
        return 'protected_child_method';
    }
    
    private static function static_private_child_method()
    {
        return 'static_private_child_method';
    }

    private static function static_private_method()
    {
        return 'static_private_child_method';
    }

    protected static function static_protected_method()
    {
        return 'static_protected_child_method';
    }

}