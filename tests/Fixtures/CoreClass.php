<?php

namespace Alpa\Tools\Tests\Sucker\Fixtures;

class CoreClass
{
    private $private_core_prop='private_core_prop';
    private $private_prop='private_core_prop';
    protected $protected_prop='protected_core_prop';
    public $public_prop='public_core_prop';
    public $public_core_prop='public_core_prop';

    private static $static_private_core_prop='static_private_core_prop';
    private static$static_private_prop='static_private_core_prop';
    protected static $static_protected_prop='static_protected_core_prop';
    public static $static_public_prop='static_public_core_prop';
    public static $static_public_core_prop='static_public_core_prop';
    
    /**  @deprecated */
    public static $public_static_prop='public_static_core_prop';
    /**  @deprecated */
    public static $public_static_core_prop='public_static_core_prop';
    /**  @deprecated */
    public static $protected_static_prop='protected_static_core_prop';
    /**  @deprecated */
    protected static $protected_static_core_prop='protected_static_core_prop';
    /**  @deprecated */
    private static $private_static_prop='private_static_core_prop';
    /**  @deprecated */
    private static $private_static_core_prop='private_static_core_prop';
    
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

    /**  @deprecated */
    public function getPrivateProp()
    {
        return $this->private_prop??null;
    }
    /**  @deprecated */
    public static function getPrivateStaticProp()
    {
        return self::$private_static_prop??null;
    }



    /**  @deprecated */
    private static function private_static_core_method()
    {
        return 'private_static_core_method';
    }
    /**  @deprecated */
    private static function private_static_method()
    {
        return 'private_static_core_method';
    }
    /**  @deprecated */
    protected static function protected_static_method()
    {
        return 'protected_static_core_method';
    }
}