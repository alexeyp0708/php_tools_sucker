<?php


namespace Alpa\Tools\Tests\Sucker;

use Alpa\Tools\Sucker\SuckerObjectHandlers;
use Alpa\Tools\Tests\Sucker\Fixtures;
use Alpa\PhpunitHelpers\Assertions\AdditionalAssertionsTrait as Assert;

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
        self::$fixtures['subject'] = $obj;
    }

    public static function providerByProperties(): array
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
            ['public_core_prop', null, 'public_core_prop'],//6
            // test parents  object properties
            // Scope ChildClass class
            ['public_prop', Fixtures\ChildClass::class, 'public_subchild_prop'],//7
            ['public_subchild_prop', Fixtures\ChildClass::class, 'public_subchild_prop'],//8
            ['public_child_prop', Fixtures\ChildClass::class, 'public_child_prop'],//9
            ['public_core_prop', Fixtures\ChildClass::class, 'public_core_prop'],//10
            ['private_child_prop', Fixtures\ChildClass::class, 'private_child_prop'],//11
            ['private_prop', Fixtures\ChildClass::class, 'private_child_prop'],//12
            ['protected_prop', Fixtures\ChildClass::class, 'protected_child_prop'],//13

            //Scope CoreClass class
            ['private_core_prop', Fixtures\CoreClass::class, 'private_core_prop'],//14
            ['private_prop', Fixtures\CoreClass::class, 'private_core_prop'],//15
            ['public_core_prop', Fixtures\CoreClass::class, 'public_core_prop'],//16
            ['public_subchild_prop', Fixtures\CoreClass::class, 'public_subchild_prop'],//17
            ['public_child_prop', Fixtures\CoreClass::class, 'public_child_prop'],//18
            //own ChildClass class
            ['protected_prop', Fixtures\CoreClass::class, 'protected_child_prop'],//20
            ['public_prop', Fixtures\CoreClass::class, 'public_subchild_prop'],//21
            ['public_core_prop', Fixtures\CoreClass::class, 'public_core_prop'],//22
            //own SubChildClass class
            ['public_prop', Fixtures\CoreClass::class, 'public_subchild_prop'],//23
        ];
    }

    public static function providerByScopes(): array
    {
        return [
            [Fixtures\SubChildClass::class],
            [Fixtures\ChildClass::class],
            [Fixtures\CoreClass::class],
        ];
    }

    /**
     * @dataProvider providerByProperties
     */
    public function test_getByReference(string $property, ?string $scope = null, $expected = '')
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

    /**
     * @dataProvider providerByScopes
     */
    public function test_getErrors($scope)
    {
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope($scope);


        //Test for an error when a property does not exist in an object
        $this->assertTrue(
            Assert::isError(
                fn() => $handlers->get('no_property'),
                'Undefined property',
                E_NOTICE | E_WARNING
            ),
            'Test for generating an error when a property is missing'
        );
        $this->assertTrue(
            Assert::isError(
                fn() => $handlers->get('no_property'),
                'Undefined property',
                E_NOTICE | E_WARNING
            ),
            'We check whether the property was not created after checking for absence'
        );
    }

    /**
     * @dataProvider providerByProperties
     */
    public function test_setByReference(string $property, ?string $scope = null, $expected = '')
    {
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope($scope);
        $restore = $handlers->get($property);
        $value = 'changed';
        $handlers->set($property, $value);
        $this->assertSame($value, $handlers->get($property));
        $value = $restore;
        $this->assertSame($expected, $handlers->get($property));
        unset($var);
    }

    public static function providerByMethods(): array
    {
        return [
            ['private_subchild_method', null, 'private_subchild_method'],//0
            ['private_method', null, 'private_subchild_method'],//1
            ['protected_method', null, 'protected_child_method'],//2
            ['private_child_method', Fixtures\ChildClass::class, 'private_child_method'],//3
            ['private_method', Fixtures\ChildClass::class, 'private_child_method'],//4
            ['protected_method', Fixtures\ChildClass::class, 'protected_child_method'],//5
            ['private_core_method', Fixtures\CoreClass::class, 'private_core_method'],//6
            ['private_method', Fixtures\CoreClass::class, 'private_core_method'],//7
        ];
    }

    /**
     * @dataProvider providerByMethods
     */
    public function test_call(string $method, ?string $scope, $expected)
    {
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope($scope);
        $this->assertSame($expected, $handlers->call($method));
    }

    public function test_callByReference()
    {
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope(Fixtures\CoreClass::class);
        $var1 = 'hello';
        $var2 =& $handlers->call('testReference', $var1);
        $var2 = 'bay';
        $this->assertSame($var2, $var1);
    }

    /**
     * @dataProvider providerByScopes
     */
    public function test_each($scope)
    {
        $expected = [$scope => []];
        foreach (self::providerByProperties() as $value) {
            if ($scope === ($value[1] ?? Fixtures\SubChildClass::class)) {
                $expected[$scope][$value[0]] = $value[2];
            }
        }
        $tester = $this;
        $handlers = self::$fixtures['handlers'];
        $object = self::$fixtures['subject'];
        $handlers->setScope($scope);
        $check = 0;
        $handlers->each(function ($key, $value) use (&$check, $tester, $scope, $object) {
            $tester->assertSame($scope, self::class);
            $tester->assertTrue($this === $object);
            if ($check === 0) {
                $check++;
            }
            return true;//break 
        });
        $tester->assertTrue(1 === $check, 'Test break foreach (return true)');

        $handlers->each(function ($key, $value) use ($expected, $tester, $scope) {
            $tester->assertSame($scope, self::class);
            $tester->assertSame($expected[self::class][$key], $value);
        });
    }

    public function test_eachByReference()
    {
        $tester = $this;
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope(null);
        $handlers->each(function ($key, &$value) use ($tester, $handlers, &$check) {
            $buf = $value;
            $value = 'changed';
            $tester->assertSame($value, $handlers->get($key));
            $value = $buf;
            $tester->assertSame($value, $handlers->get($key));
            return true;
        });
    }

    public function test_isset()
    {
        $handlers = self::$fixtures['handlers'];

        $handlers->setScope(null);
        $this->assertFalse($handlers->isset('no_property'));
        $this->assertTrue($handlers->isset('private_subchild_prop'));
        $this->assertFalse($handlers->isset('private_child_prop'));

        $handlers->setScope(Fixtures\ChildClass::class);
        $this->assertTrue($handlers->isset('private_child_prop'));
        $this->assertFalse($handlers->isset('private_core_prop'));

        $handlers->setScope(Fixtures\CoreClass::class);
        $this->assertTrue($handlers->isset('private_core_prop'));
        $this->assertFalse($handlers->isset('private_subchild_prop'));
        $this->assertFalse($handlers->isset('private_child_prop'));
    }

    public function test_unset()
    {
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope(null);
        $this->assertTrue($handlers->isset('private_subchild_prop'));
        $handlers->unset("private_subchild_prop");
        $this->assertFalse($handlers->isset('private_subchild_prop'));
    }

    /**
     * @dataProvider providerByScopes
     */
    public function test_sandbox($scope)
    {
        $handlers = self::$fixtures['handlers'];
        $subject = self::$fixtures['subject'];
        $handlers->setScope($scope);
        $tester = $this;
        $answer =& $handlers->sandbox(function (...$args) use ($tester, $scope, $subject) {
            $tester->assertSame($scope, self::class);
            $tester->assertTrue($subject === $this);
            return $args[0];
        }, ['hello']);
        $tester->assertSame('hello', $answer);
    }

    public function test_sandboxByReference()
    {
        $handlers = self::$fixtures['handlers'];
        $arg = 'hello';
        $answer =& $handlers->sandbox(function & (&...$args) {
            return $args[0];
        }, [&$arg]);
        $arg = 'bay';
        $this->assertTrue($arg === $answer);
    }
}