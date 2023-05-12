<?php

namespace Alpa\Tools\Tests\Sucker\Fixtures;

class CoreClass
{
    private $private_core_prop='private_core_prop';
    private $private_prop='private_core_prop';
    public $public_prop='public_core_prop';
    public $public_core_prop='public_core_prop';
    public $protected_prop='protected_core_prop';
    
    public static $public_static_prop='public_static_core_prop';
    public static $protected_static_prop='protected_static_core_prop';
    public static $protected_static_core_prop='protected_static_core_prop';
    private static $private_static_prop='private_static_core_prop';
    private static $private_static_core_prop='private_static_core_prop';
    
    private function private_core_method()
    {
        return 'private_core_method';
    }

    private function private_method()
    {
        return 'private_core_method';
    }
    public function getPrivateProp()
    {
        return $this->private_prop??null;
    }
    public static function getPrivateStaticProp()
    {
        return self::$private_static_prop??null;
    }
    private static function private_static_core_method()
    {
        return 'private_static_core_method';
    }
    private static function private_static_method()
    {
        return 'private_static_core_method';
    }   
    protected static function protected_static_method()
    {
        return 'protected_static_core_method';
    }
}