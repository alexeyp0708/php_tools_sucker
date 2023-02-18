<?php

namespace Alpa\Tools\Tests\Sucker\Fixtures;

class ChildClass extends CoreClass
{
    private $private_child_prop='private_child_prop';
    private $private_prop='private_child_prop';
    public $public_prop='public_child_prop';
    public $public_child_prop='public_child_prop';

    public static $public_static_prop='public_static_child_prop';
    public static $public_static_child_prop='public_static_child_prop';
    private static $private_static_prop='private_static_child_prop';
    private static $private_static_child_prop='private_static_child_prop';
    
    private function private_child_method()
    {
        return 'private_child_method';
    }
    private function private_method()
    {
        return 'private_child_method';
    }
    private static function private_static_child_method()
    {
        return 'private_static_child_method';
    }
    private static function private_static_method()
    {
        return 'private_static_child_method';
    }
}