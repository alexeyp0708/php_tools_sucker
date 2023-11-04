<?php


namespace Alpa\Tools\Tests\Sucker;

use Alpa\PhpunitHelpers\Assertions\AdditionalAssertionsTrait as Assert;
use Alpa\Tools\Tests\Sucker\Fixtures;
use Alpa\Tools\Sucker\Proxy;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{
    private static array $fixtures = [];

    public static function setUpBeforeClass(): void
    {
        $obj = new Fixtures\SubChildClass();
        $handlers = new Proxy($obj);
        self::$fixtures['handlers'] = $handlers;
        self::$fixtures['subject'] = $obj;
        self::$fixtures['staticHandlers'] = new Proxy(get_class($obj));
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
        $var =& $handlers($scope)->$property;
        $var = strtoupper($var);
        $this->assertSame(strtoupper($expected), $handlers($scope)->$property);
        $var = strtolower($var);
        $this->assertSame($expected, $handlers($scope)->$property);
        unset($var);
        $this->assertSame('static_private_subchild_prop', $handlers->static_private_subchild_prop, 'check reset scope');
    }

    /**
     * @dataProvider providerByProperties
     */
    public function test_getByReference(string $property, ?string $scope = null, $expected = '')
    {
        $handlers = self::$fixtures['handlers'];
        $var =& $handlers($scope)->$property;
        $var = strtoupper($var);
        $this->assertSame(strtoupper($expected), $handlers($scope)->$property);
        $var = strtolower($var);
        $this->assertSame($expected, $handlers($scope)->$property);
        unset($var);
        $this->assertSame('private_subchild_prop', $handlers->private_subchild_prop, 'check reset scope');
    }

    /**
     * @dataProvider providerByProperties
     */
    public function test_set(string $property, ?string $scope = null, $expected = '')
    {
        $handlers = self::$fixtures['handlers'];
        $restore = $handlers($scope)->$property;
        $handlers($scope)->$property = 'changed';
        $this->assertSame('changed', $handlers($scope)->$property);
        $handlers($scope)->$property = $restore;
        $this->assertSame($expected, $handlers($scope)->$property);
        $this->assertSame('private_subchild_prop', $handlers->private_subchild_prop, 'check reset scope');
    }


    /**
     * @dataProvider providerByStaticProperties
     */
    public function test_staticSet(string $property, ?string $scope = null, $expected = '')
    {
        $handlers = self::$fixtures['staticHandlers'];
        $restore = $handlers($scope)->$property;
        $handlers($scope)->$property = 'changed';
        $this->assertSame('changed', $handlers($scope)->$property);
        $handlers($scope)->$property = $restore;
        $this->assertSame($expected, $handlers($scope)->$property);
        $this->assertSame('static_private_subchild_prop', $handlers->static_private_subchild_prop, 'check reset scope');
    }

    /**
     * @dataProvider providerByMethods
     */
    public function test_call(string $method, ?string $scope, $expected)
    {
        $handlers = self::$fixtures['handlers'];
        $this->assertSame($expected, $handlers($scope)->$method());
        $this->assertSame('private_subchild_prop', $handlers->private_subchild_prop, 'check reset scope');
    }

    /**
     * @dataProvider providerByStaticMethods
     */
    public function test_staticCall(string $method, ?string $scope, $expected)
    {
        $handlers = self::$fixtures['staticHandlers'];
        $this->assertSame($expected, $handlers($scope)->$method());
        $this->assertSame('static_private_subchild_prop', $handlers->static_private_subchild_prop, 'check reset scope');
    }

    public function test_returnCallByReference()
    {
        $handlers = self::$fixtures['handlers'];
        $obj = (object)['prop' => 'hello'];
        $var2 =& $handlers(Fixtures\CoreClass::class)->testReturnReference($obj);
        $var2 = 'bay';
        $this->assertSame($var2, $obj->prop);
        $this->assertSame('private_subchild_prop', $handlers->private_subchild_prop, 'check reset scope');
    }


    public function test_isset()
    {
        $handlers = self::$fixtures['handlers'];

        $this->assertFalse(isset($handlers->no_property));
        $this->assertTrue(isset($handlers->private_subchild_prop));
        $this->assertFalse(isset($handlers->private_child_prop));

        $this->assertTrue(isset($handlers(Fixtures\ChildClass::class)->private_child_prop));
        $this->assertFalse(isset($handlers(Fixtures\ChildClass::class)->private_core_prop));

        $this->assertTrue(isset($handlers(Fixtures\CoreClass::class)->private_core_prop));
        $this->assertFalse(isset($handlers(Fixtures\CoreClass::class)->private_subchild_prop));
        $this->assertFalse(isset($handlers(Fixtures\CoreClass::class)->private_child_prop));

        $this->assertSame('private_subchild_prop', $handlers->private_subchild_prop, 'check reset scope');
    }

    public function test_staticIsset()
    {
        $tester = $this;
        $handlers = self::$fixtures['staticHandlers'];

        $this->assertFalse(isset($handlers->no_property));
        $this->assertTrue(isset($handlers->static_private_subchild_prop));
        $this->assertFalse(isset($handlers->static_private_child_prop));

        $this->assertTrue(isset($handlers(Fixtures\ChildClass::class)->static_private_child_prop));
        $this->assertFalse(isset($handlers(Fixtures\ChildClass::class)->static_private_core_prop));

        $this->assertTrue(isset($handlers(Fixtures\CoreClass::class)->static_private_core_prop));
        $this->assertFalse(isset($handlers(Fixtures\CoreClass::class)->static_private_subchild_prop));
        $this->assertFalse(isset($handlers(Fixtures\CoreClass::class)->static_private_child_prop));

        $this->assertSame('static_private_subchild_prop', $handlers->static_private_subchild_prop, 'check reset scope');
    }

    /**
     * @dataProvider providerByScopes
     */
    public function test_foreach($scope)
    {
        $expected = [$scope => []];
        foreach (self::providerByProperties() as $value) {
            if ($scope === ($value[1] ?? Fixtures\SubChildClass::class)) {
                $expected[$scope][$value[0]] = $value[2];
            }
        }
        $tester = $this;
        $handlers = self::$fixtures['handlers'];

        foreach ($handlers($scope) as $key => $value) {
            $tester->assertSame($expected[$scope][$key], $value);
        }
        $this->assertSame('private_subchild_prop', $handlers->private_subchild_prop, 'check reset scope');
    }

    /**
     * @dataProvider providerByScopes
     */
    public function test_staticForeach(string $scope)
    {
        $expected = [$scope => []];
        foreach (self::providerByStaticProperties() as $value) {
            if ($scope === ($value[1] ?? Fixtures\SubChildClass::class)) {
                $expected[$scope][$value[0]] = $value[2];
            }
        }
        $tester = $this;
        $handlers = self::$fixtures['staticHandlers'];

        foreach ($handlers($scope) as $key => $value) {
            $tester->assertSame($expected[$scope][$key], $value);
        }

        $this->assertSame('static_private_subchild_prop', $handlers->static_private_subchild_prop, 'check reset scope');
    }

    public function test_unset()
    {
        $handlers = self::$fixtures['handlers'];
        $this->assertTrue(isset($handlers(Fixtures\ChildClass::class)->private_child_prop));
        $buf = $handlers(Fixtures\ChildClass::class)->private_child_prop;
        unset($handlers(Fixtures\ChildClass::class)->private_child_prop);
        $this->assertFalse(isset($handlers(Fixtures\ChildClass::class)->private_child_prop));
        $handlers(Fixtures\ChildClass::class)->private_child_prop = $buf;
        $this->assertSame('private_subchild_prop', $handlers->private_subchild_prop, 'check reset scope');
    }

    public function test_staticUnset()
    {
        $handlers = self::$fixtures['staticHandlers'];
        $check = Assert::isError(function () use ($handlers) {
            unset($handlers(Fixtures\ChildClass::class)->static_private_child_prop);
        }, 'Attempt to unset static property');
        $this->assertTrue($check);
        $this->assertSame('static_private_subchild_prop', $handlers->static_private_subchild_prop, 'check reset scope');
    }

    /**
     * @dataProvider providerByScopes
     */
    public function test_sandbox($scope)
    {
        $handlers = self::$fixtures['handlers'];
        $subject = self::$fixtures['subject'];
        $tester = $this;
        $answer =& $handlers($scope)(function (...$args) use ($tester, $scope, $subject) {
            $tester->assertSame($scope, self::class);
            $tester->assertTrue($subject === $this);
            return $args[0];
        }, ['hello']);
        $tester->assertSame('hello', $answer);
        $this->assertSame('private_subchild_prop', $handlers->private_subchild_prop, 'check reset scope');
    }

    /**
     * @dataProvider providerByScopes
     */
    public function test_staticSandbox($scope)
    {
        $handlers = self::$fixtures['staticHandlers'];
        $tester = $this;
        $answer =& $handlers($scope)(function (...$args) use ($tester, $scope) {
            $tester->assertSame($scope, self::class);
            return $args[0];
        }, ['hello']);
        $tester->assertSame('hello', $answer);
        $this->assertSame('static_private_subchild_prop', $handlers->static_private_subchild_prop, 'check reset scope');
    }

    public function test_sandboxByReference()
    {
        $handlers = self::$fixtures['handlers'];
        $arg = 'hello';
        $answer =& $handlers(function & (&...$args) {
            return $args[0];
        }, [&$arg]);
        $arg = 'bay';
        $this->assertTrue($arg === $answer);
    }
}