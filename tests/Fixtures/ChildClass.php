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

    /**
     * @deprecated
     */
    public static $public_static_prop = 'public_static_child_prop';
    /**
     * @deprecated
     */
    public static $protected_static_prop = 'protected_static_child_prop';
    /**
     * @deprecated
     */
    public static $public_static_child_prop = 'public_static_child_prop';
    /**
     * @deprecated
     */
    protected static $protected_static_child_prop = 'protected_static_child_prop';
    /**
     * @deprecated
     */
    private static $private_static_prop = 'private_static_child_prop';
    /**
     * @deprecated
     */
    private static $private_static_child_prop = 'private_static_child_prop';

    
    private function private_child_method()
    {
        return 'private_child_method';
    }

    private function private_method()
    {
        return 'private_child_method';
    }

    protected  function protected_method()
    {
        return 'protected_child_method';
    }

    /** @deprecated */
    protected function returns_args(...$args)
    {
        return $args;
    }
    /** @deprecated */
    protected function & ref_method(&$arg)
    {
        return $arg;
    }
    /** @deprecated */
    protected function & return_args_method(&...$args)
    {
        return $args;
    }
    /** @deprecated */
    protected function & ref_ref_static_method(&$arg)
    {
        return $arg;
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



    /** @deprecated */
    private static function private_static_child_method()
    {
        return 'private_static_child_method';
    }
    /** @deprecated */
    private static function private_static_method()
    {
        return 'private_static_child_method';
    }
    /** @deprecated */
    protected static function protected_static_method()
    {
        return 'protected_static_child_method';
    }
    /** @deprecated */
    protected static function static_returns_args(...$args)
    {
        return $args;
    }
}