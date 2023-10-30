<?php


namespace Alpa\Tools\Tests\Sucker;

use Alpa\Tools\Sucker\SuckerObjectHandlers;
use Alpa\Tools\Tests\Sucker\Fixtures;

class SuckerObjectHandlersTest extends \PHPUnit\Framework\TestCase
{
    private static array $fixtures = [];

    public function setUp(): void
    {

    }

    public static function setUpBeforeClass(): void
    {
        $obj = new Fixtures\SubChildClass();
        $handlers = new SuckerObjectHandlers();
        $handlers->setSubject($obj);
        self::$fixtures['handlers'] = $handlers;
    }

    public static function providerForGet(): array
    {
        return [ //[property,?scope,expected]            
            // test current class object properties 
            //Scope SubChildClass class
            // own SubChildClass class
            ['private_subchild_prop', null, 'private_subchild_prop'],//0
            ['private_prop', null, 'private_subchild_prop'],//1
            ['public_prop', null, 'public_subchild_prop'],//2
            ['public_subchild_prop', null, 'public_subchild_prop'],//3
            // own ChildClass class
            ['protected_prop', null, 'protected_child_prop'],//4
            ['public_child_prop', null, 'public_child_prop'],//5
            // own CoreClass class
            ['protected_prop', null, 'protected_child_prop'],//6
            ['public_core_prop', null, 'public_core_prop'],//7
            ['protected_prop', null, 'protected_child_prop'],//8
            ['public_core_prop', null, 'public_core_prop'],//9
            // test parents  object properties
            // Scope ChildClass class
            // own SubChildClass class
            ['public_prop', Fixtures\ChildClass::class, 'public_subchild_prop'],//10
            // own ChildClass class
            ['private_child_prop', Fixtures\ChildClass::class, 'private_child_prop'],//11
            ['private_prop', Fixtures\ChildClass::class, 'private_child_prop'],//12
            ['protected_prop', Fixtures\ChildClass::class, 'protected_child_prop'],//13
            //Scope CoreClass class
            //own CoreClass class
            ['private_core_prop', Fixtures\CoreClass::class, 'private_core_prop'],//14
            ['private_prop', Fixtures\CoreClass::class, 'private_core_prop'],//15
            ['public_core_prop', Fixtures\CoreClass::class, 'public_core_prop'],//16
            //own ChildClass class
            ['protected_prop', Fixtures\CoreClass::class, 'protected_child_prop'],//17
            ['public_prop', Fixtures\CoreClass::class, 'public_subchild_prop'],//18
            ['public_core_prop', Fixtures\CoreClass::class, 'public_core_prop'],//19
            //own SubChildClass class
            ['public_prop', Fixtures\CoreClass::class, 'public_subchild_prop'],//19
        ];
    }

    /**
     * @dataProvider providerForGet
     */
    public function test_get(?string $property, ?string $scope = null, $expected='')
    {
        $handlers = self::$fixtures['handlers'];    

        $this->assertSame($expected, $handlers->setScope($scope)->get($property));
    }

    /**
     * @dataProvider providerForGet
     */
    public function test_getByReference(string $property, ?string $scope = null, $expected='')
    {
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope($scope);
        $var =& $handlers->get($property);
        $var = strtoupper($var);
        $this->assertSame(strtoupper($expected), $handlers->get($property));
        $var = strtolower($var);
        $this->assertSame($expected, $handlers->get($property));
        unset($var);
    }
    public static function providerByScopes():array
    {
        return [
            [Fixtures\SubChildClass::class],
            [Fixtures\ChildClass::class],
            [Fixtures\CoreClass::class],
        ];
    }

    /**
     * @dataProvider providerByScopes
     */
    public function test_getErrors($scope)
    {
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope($scope);

        //Test for an error when a property does not exist in an object
        $check=false;
        set_error_handler(function(...$args) use (&$check){
            if(substr($args[1],0,18)==='Undefined property'){
                $check=true;
                return true;
            }
            return false;
        },E_USER_WARNING|E_USER_NOTICE);
        $handlers->get('no_property');
        restore_error_handler();
        $this->assertTrue($check,'Test for generating an error when a property is missing');
        //Recheck. We check whether the property was not created after checking for absence
        $check=false;
        set_error_handler(function(...$args) use (&$check){
            if(substr($args[1],0,18)==='Undefined property'){
                $check=true;
                return true;
            }
            return false;
        },E_USER_WARNING|E_USER_NOTICE);
        $handlers->get('no_property');
        restore_error_handler();
        $this->assertTrue($check,'We check whether the property was not created after checking for absence');
        
    }

    public function test_set()
    {

    }

    public function test_setByReference()
    {

    }

    public function test_call()
    {

    }

    public function test_callByReference()
    {
        
    }

    public function test_each()
    {

    }

    public function test_eachByReference()
    {

    }

    public function test_isset()
    {

    }

    public function test_unset()
    {

    }

    public function test_sandbox()
    {

    }

    public function test_sandboxByReference()
    {

    }
}