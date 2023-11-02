<?php

namespace Alpa\Tools\Tests\Sucker;

use Alpa\Tools\Sucker\SuckerObjectHandlers;
use Alpa\Tools\Tests\Sucker\Fixtures\MyClass;
use Alpa\Tools\Tests\Sucker\Fixtures\ChildClass;
use Alpa\Tools\Tests\Sucker\Fixtures\CoreClass;
use Alpa\Tools\Tests\Sucker\Fixtures\SubChildClass;
use Alpa\Tools\Tests\Sucker\Fixtures\Sucker;
use PHPUnit\Framework\TestCase;
use Alpa\PhpunitHelpers\Assertions\AdditionalAssertionsTrait as Assert;

class SuckerTest extends TestCase
{
    private static array $fixtures = [];

    public static function setUpBeforeClass(): void
    {
        $obj = new Fixtures\SubChildClass();
        $handlers = new \Alpa\Tools\Sucker\Sucker($obj);
        self::$fixtures['handlers'] = $handlers;
        self::$fixtures['subject'] = $obj;
        self::$fixtures['staticHandlers'] = new \Alpa\Tools\Sucker\Sucker(get_class($obj));
    }

    public static function providerByProperties(): array
    {
        return SuckerObjectHandlersTest::providerByProperties();
    }

    public static function providerByStaticProperties(): array
    {
        return SuckerClassHandlersTest::providerByProperties();
    }

    public static function providerByScopes(): array
    {
        return SuckerObjectHandlersTest::providerByScopes();
    }

    public static function providerByMethods(): array
    {
        return SuckerObjectHandlersTest::providerByMethods();
    }

    public static function providerByStaticMethods(): array
    {
        return SuckerClassHandlersTest::providerByMethods();
    }

    /**
     * @dataProvider providerByStaticProperties
     */
    public function test_staticGetByReference(string $property, ?string $scope = null, $expected = '')
    {
        $handlers = self::$fixtures['staticHandlers'];
        $var =& $handlers($scope)->get($property);
        $var = strtoupper($var);
        $this->assertSame(strtoupper($expected), $handlers($scope)->get($property));
        $var = strtolower($var);
        $this->assertSame($expected, $handlers($scope)->get($property));
        unset($var);
        $this->assertSame('static_private_subchild_prop', $handlers->get('static_private_subchild_prop'), 'check reset scope');
    }

    /**
     * @dataProvider providerByProperties
     */
    public function test_getByReference(string $property, ?string $scope = null, $expected = '')
    {
        $handlers = self::$fixtures['handlers'];
        $var =& $handlers($scope)->get($property);
        $var = strtoupper($var);
        $this->assertSame(strtoupper($expected), $handlers($scope)->get($property));
        $var = strtolower($var);
        $this->assertSame($expected, $handlers($scope)->get($property));
        unset($var);
        $this->assertSame('private_subchild_prop', $handlers->get('private_subchild_prop'), 'check reset scope');
    }

    /**
     * @dataProvider providerByProperties
     */
    public function test_set(string $property, ?string $scope = null, $expected = '')
    {
        $handlers = self::$fixtures['handlers'];
        $restore = $handlers($scope)->get($property);
        $handlers($scope)->set($property, 'changed');
        $this->assertSame('changed', $handlers($scope)->get($property));
        $handlers($scope)->set($property, $restore);
        $this->assertSame($expected, $handlers($scope)->get($property));
        $this->assertSame('private_subchild_prop', $handlers->get('private_subchild_prop'), 'check reset scope');
    }

    /**
     * @dataProvider providerByProperties
     */
    public function test_setByReference(string $property, ?string $scope = null, $expected = '')
    {
        $handlers = self::$fixtures['handlers'];
        $restore = $handlers($scope)->get($property);
        $value = 'changed';
        $handlers($scope)->setRef($property, $value);
        $this->assertSame($value, $handlers($scope)->get($property));
        $value = $restore;
        $this->assertSame($expected, $handlers($scope)->get($property));
        $this->assertSame('private_subchild_prop', $handlers->get('private_subchild_prop'), 'check reset scope');
    }

    /**
     * @dataProvider providerByStaticProperties
     */
    public function test_staticSet(string $property, ?string $scope = null, $expected = '')
    {
        $handlers = self::$fixtures['staticHandlers'];
        $restore = $handlers($scope)->get($property);
        $handlers($scope)->set($property, 'changed');
        $this->assertSame('changed', $handlers($scope)->get($property));
        $handlers($scope)->set($property, $restore);
        $this->assertSame($expected, $handlers($scope)->get($property));
        $this->assertSame('static_private_subchild_prop', $handlers->get('static_private_subchild_prop'), 'check reset scope');
    }

    /**
     * @dataProvider providerByStaticProperties
     */
    public function test_staticSetByReference(string $property, ?string $scope = null, $expected = '')
    {
        $handlers = self::$fixtures['staticHandlers'];
        $restore = $handlers($scope)->get($property);
        $value = 'changed';
        $handlers($scope)->setRef($property, $value);
        $this->assertSame($value, $handlers($scope)->get($property));
        $value = $restore;
        $this->assertSame($expected, $handlers($scope)->get($property));
        $this->assertSame('static_private_subchild_prop', $handlers->get('static_private_subchild_prop'), 'check reset scope');
    }

    /**
     * @dataProvider providerByMethods
     */
    public function test_call(string $method, ?string $scope, $expected)
    {
        $handlers = self::$fixtures['handlers'];
        $this->assertSame($expected, $handlers($scope)->call($method));
        $this->assertSame('private_subchild_prop', $handlers->get('private_subchild_prop'), 'check reset scope');
    }

    /**
     * @dataProvider providerByStaticMethods
     */
    public function test_staticCall(string $method, ?string $scope, $expected)
    {
        $handlers = self::$fixtures['staticHandlers'];
        $this->assertSame($expected, $handlers($scope)->call($method));
        $this->assertSame('static_private_subchild_prop', $handlers->get('static_private_subchild_prop'), 'check reset scope');
    }

    public function test_apply()
    {
        $handlers = self::$fixtures['handlers'];
        $var1 = 'hello';
        $var2 =& $handlers(Fixtures\CoreClass::class)->apply('testReference', [&$var1]);
        $var2 = 'bay';
        $this->assertSame($var2, $var1);
        $this->assertSame('private_subchild_prop', $handlers->get('private_subchild_prop'), 'check reset scope');
    }


    public function test_isset()
    {
        $handlers = self::$fixtures['handlers'];

        $this->assertFalse($handlers->isset('no_property'));
        $this->assertTrue($handlers->isset('private_subchild_prop'));
        $this->assertFalse($handlers->isset('private_child_prop'));

        $this->assertTrue($handlers(Fixtures\ChildClass::class)->isset('private_child_prop'));
        $this->assertFalse($handlers(Fixtures\ChildClass::class)->isset('private_core_prop'));

        $this->assertTrue($handlers(Fixtures\CoreClass::class)->isset('private_core_prop'));
        $this->assertFalse($handlers(Fixtures\CoreClass::class)->isset('private_subchild_prop'));
        $this->assertFalse($handlers(Fixtures\CoreClass::class)->isset('private_child_prop'));

        $this->assertSame('private_subchild_prop', $handlers->get('private_subchild_prop'), 'check reset scope');
    }

    public function test_staticIsset()
    {
        $tester = $this;
        $handlers = self::$fixtures['staticHandlers'];

        $this->assertFalse($handlers->isset('no_property'));
        $this->assertTrue($handlers->isset('static_private_subchild_prop'));
        $this->assertFalse($handlers->isset('static_private_child_prop'));

        $this->assertTrue($handlers(Fixtures\ChildClass::class)->isset('static_private_child_prop'));
        $this->assertFalse($handlers(Fixtures\ChildClass::class)->isset('static_private_core_prop'));

        $this->assertTrue($handlers(Fixtures\CoreClass::class)->isset('static_private_core_prop'));
        $this->assertFalse($handlers(Fixtures\CoreClass::class)->isset('static_private_subchild_prop'));
        $this->assertFalse($handlers(Fixtures\CoreClass::class)->isset('static_private_child_prop'));

        $this->assertSame('static_private_subchild_prop', $handlers->get('static_private_subchild_prop'), 'check reset scope');
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
        $check = 0;
        $handlers($scope)->each(function ($key, $value) use (&$check, $tester, $scope, $object) {
            $tester->assertSame($scope, self::class);
            $tester->assertTrue($this === $object);
            if ($check === 0) {
                $check++;
            }
            return true;//break 
        });
        $tester->assertTrue(1 === $check, 'Test break foreach (return true)');

        $handlers($scope)->each(function ($key, $value) use ($expected, $tester, $scope) {
            $tester->assertSame($scope, self::class);
            $tester->assertSame($expected[self::class][$key], $value);
        });
        $this->assertSame('private_subchild_prop', $handlers->get('private_subchild_prop'), 'check reset scope');
    }

    /**
     * @dataProvider providerByScopes
     */
    public function test_staticEach($scope)
    {
        $expected = [$scope => []];
        foreach (self::providerByStaticProperties() as $value) {
            if ($scope === ($value[1] ?? Fixtures\SubChildClass::class)) {
                $expected[$scope][$value[0]] = $value[2];
            }
        }
        $tester = $this;
        $handlers = self::$fixtures['staticHandlers'];

        $check = 0;
        $handlers($scope)->each(function ($key, $value) use (&$check, $tester, $scope) {
            $tester->assertSame($scope, self::class);
            if ($check === 0) {
                $check++;
            }
            return true;//break 
        });
        $tester->assertTrue(1 === $check, 'Test break foreach (return true)');
        $handlers($scope)->each(function ($key, $value) use ($expected, $tester, $scope) {
            /** @deprecated */
            if (substr($key, 0, 7) !== 'static_') {
                return;
            }
            $tester->assertSame($scope, self::class);
            $tester->assertSame($expected[self::class][$key], $value);
        });
        $this->assertSame('static_private_subchild_prop', $handlers->get('static_private_subchild_prop'), 'check reset scope');

    }

    public function test_eachByReference()
    {
        $tester = $this;
        $handlers = self::$fixtures['handlers'];
        $handlers->each(function ($key, &$value) use ($tester, $handlers, &$check) {
            $buf = $value;
            $value = 'changed';
            $tester->assertSame($value, $handlers->get($key));
            $value = $buf;
            $tester->assertSame($value, $handlers->get($key));
            return true;
        });
    }

    public function test_unset()
    {
        $handlers = self::$fixtures['handlers'];
        $this->assertTrue($handlers(ChildClass::class)->isset('private_child_prop'));
        $buf = $handlers(ChildClass::class)->get('private_child_prop');
        $handlers(ChildClass::class)->unset("private_child_prop");
        $this->assertFalse($handlers(ChildClass::class)->isset('private_child_prop'));
        $handlers(ChildClass::class)->set('private_child_prop', $buf);

        $this->assertSame('private_subchild_prop', $handlers->get('private_subchild_prop'), 'check reset scope');

    }

    public function test_staticUnset()
    {
        $handlers = self::$fixtures['staticHandlers'];
        $check = Assert::isError(fn() => $handlers(ChildClass::class)->unset('static_private_child_prop'), 'Attempt to unset static property');
        $this->assertTrue($check);
        $this->assertSame('static_private_subchild_prop', $handlers->get('static_private_subchild_prop'), 'check reset scope');
    }

    /**
     * @dataProvider providerByScopes
     */
    public function test_sandbox($scope)
    {
        $handlers = self::$fixtures['handlers'];
        $subject = self::$fixtures['subject'];
        $tester = $this;
        $answer =& $handlers($scope)->sandbox(function (...$args) use ($tester, $scope, $subject) {
            $tester->assertSame($scope, self::class);
            $tester->assertTrue($subject === $this);
            return $args[0];
        }, ['hello']);
        $tester->assertSame('hello', $answer);
        $this->assertSame('private_subchild_prop', $handlers->get('private_subchild_prop'), 'check reset scope');

    }

    /**
     * @dataProvider providerByScopes
     */
    public function test_staticSandbox($scope)
    {
        $handlers = self::$fixtures['staticHandlers'];
        $tester = $this;
        $answer =& $handlers($scope)->sandbox(function (...$args) use ($tester, $scope) {
            $tester->assertSame($scope, self::class);
            return $args[0];
        }, ['hello']);
        $tester->assertSame('hello', $answer);
        $this->assertSame('static_private_subchild_prop', $handlers->get('static_private_subchild_prop'), 'check reset scope');
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