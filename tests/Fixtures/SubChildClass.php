<?php

namespace Alpa\Tools\Tests\Sucker\Fixtures;

class SubChildClass extends ChildClass
{
    private $private_subchild_prop = 'private_subchild_prop';
    private $private_prop = 'private_subchild_prop';
    public $public_prop = 'public_subchild_prop';
    public $public_subchild_prop = 'public_subchild_prop';

    private static $static_private_subchild_prop = 'static_private_subchild_prop';
    private static $static_private_prop = 'static_private_subchild_prop';
    public static $static_public_prop = 'static_public_subchild_prop';
    public static $static_public_subchild_prop = 'static_public_subchild_prop';

    /**
     * @deprecated
     */
    public static $public_static_prop = 'public_static_subchild_prop';
    /**
     * @deprecated
     */
    public static $public_static_subchild_prop = 'public_static_subchild_prop';
    /**
     * @deprecated
     */
    private static $private_static_prop = 'private_static_subchild_prop';
    /**
     * @deprecated
     */
    private static $private_static_subchild_prop = 'private_static_subchild_prop';


    private function private_subchild_method()
    {
        return 'private_subchild_method';
    }

    public function private_method()
    {
        return 'private_subchild_method';
    }

    private static function static_private_subchild_method()
    {
        return 'static_private_subchild_method';
    }

    public static function static_private_method()
    {
        return 'static_private_subchild_method';
    }

    /** @deprecated */
    private static function private_static_subchild_method()
    {
        return 'private_static_subchild_method';
    }

    /** @deprecated */
    private static function private_static_method()
    {
        return 'private_static_subchild_method';
    }
}