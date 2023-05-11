<?php

namespace Alpa\Tools\Tests\Sucker;

use Alpa\Tools\Tests\Sucker\Fixtures\MyClass;
use Alpa\Tools\Tests\Sucker\Fixtures\ChildClass;
use Alpa\Tools\Tests\Sucker\Fixtures\CoreClass;
use Alpa\Tools\Tests\Sucker\Fixtures\Sucker;
use PHPUnit\Framework\TestCase;

class SuckerTest extends TestCase
{
    public function test_static_get()
    {
        $target = ChildClass::class;
        $sucker = new Sucker($target);
        $this->assertSame($sucker->get('private_static_child_prop'), 'private_static_child_prop');
        $this->assertSame($sucker->get('private_static_prop'), 'private_static_child_prop');
        $this->assertSame($sucker->get('public_static_prop'), 'public_static_child_prop');

        $this->assertSame($sucker->get(CoreClass::class . '::private_static_core_prop'), 'private_static_core_prop');
        $this->assertSame($sucker->get(CoreClass::class . '::private_static_prop'), 'private_static_core_prop');
        $this->assertSame($sucker->get(CoreClass::class . '::public_static_prop'), 'public_static_core_prop');

        // references test 
        $ref_prop =& $sucker->get(CoreClass::class . '::private_static_prop');
        $ref_prop_val=$ref_prop;
        $ref_prop = 'changed';
        $this->assertSame($sucker->get(CoreClass::class . '::private_static_prop'), 'changed');
        $this->assertSame($sucker->get('private_static_prop'), 'private_static_child_prop');
        $ref_prop = $ref_prop_val;        
        unset($ref_prop);
        $ref_prop =& $sucker->get('private_static_prop');
        $ref_prop_val=$ref_prop;
        $ref_prop = 'changed';
        $this->assertSame($sucker->get('private_static_prop'), 'changed');
        $this->assertSame($sucker->get(CoreClass::class . '::private_static_prop'), 'private_static_core_prop');
        $ref_prop = $ref_prop_val;
        unset($ref_prop);
        $this->assertSame($sucker->get('private_static_prop'), 'private_static_child_prop');
    }

    public function test_get(){}
    
    public static function test_static_set(){}
    
    public function  test_set(){}
    
    public function test_call(){}
    
    public static function test_static_call(){}
    
    public function test_apply(){}
    
    public static function test_static_apply(){}
 
    public function test_each(){}
    
    public static function test_static_each(){}
    
    public function test_isset(){}
    
    public static function test_static_isset(){}
    
    public function test_unset(){}
    
    public static function test_static_unset(){}
    
    public function test_sandbox(){}
    
    public static function test_sbox()
    {
        $inst = new class () extends MyClass {
            private function method3()
            {
                return 'bay';
            }

            private static function method4()
            {
                return 'bay';
            }
        };
        $self = static::class;

        static::assertTrue(Sucker::sbox(function () use ($self) {
                $self::assertTrue(MyClass::class === self::class);
                $self::assertTrue($this->method3() === 'hello');
                $self::assertTrue(self::method4() === 'hello');
                return 'success';
            }, $inst, MyClass::class) === 'success');
    }

    public function test_run_action()
    {
        $target = new class() {
            private $property = 'hello';
            private $property2 = '2';
            private $property3 = '3';

            private function method($val)
            {
                return $val;
            }
        };
        $sucker = new Sucker($target);
        static::assertTrue($sucker->get('property') === 'hello');
        static::assertTrue($sucker->isset('property'));
        $sucker->set('property', 'bay');
        static::assertTrue($sucker->get('property') === 'bay');
        $sucker->unset('property');
        static::assertTrue(!$sucker->isset('property'));
        $self = $this;
        $keys = [];

        $sucker->each(function ($key, $value) use ($self, $target, &$keys) {
            $keys[] = $key;
            $self::assertTrue($this === $target);
            $self::assertTrue(self::class === get_class($target));
            $self::assertTrue($this->$key === $value);
        });
        static::assertSame($keys, ['property2', 'property3']);
        static::assertTrue($sucker->call('method', 'zzz') === 'zzz');
        static::assertTrue($sucker->sandbox(
                function ($arg) use ($target, $self) {
                    $self::assertTrue($target === $this && get_class($target) === self::class);
                    return $arg;
                }, null, ['zzz']
            ) === 'zzz'
        );
    }

    public function test_run_static_action()
    {
        $target = get_class(new class() {
            private static $property = 'hello';
            private static $property2 = '2';
            private static $property3 = '3';

            private static function method($val)
            {
                return $val;
            }
        });
        $sucker = new Sucker($target);
        static::assertTrue($sucker->get('property') === 'hello');
        static::assertTrue($sucker->isset('property'));
        $sucker->set('property', 'bay');
        static::assertTrue($sucker->get('property') === 'bay');
        try {
            $sucker->set('no_property', 'bay');
            static::assertTrue(false);
        } catch (\Error $e) {
            static::assertTrue(true);
        }
        try {
            $sucker->unset('property');
            static::assertTrue(false);
        } catch (\Error $e) {
            static::assertTrue(true);
        }
        $self = $this;
        $keys = [];
        $sucker->each(function ($key, $value) use ($self, $target, &$keys) {
            $keys[] = $key;
            $self::assertTrue(self::class === $target);
            $self::assertTrue(self::$$key === $value);
        });
        static::assertSame($keys, ['property', 'property2', 'property3']);
        static::assertTrue($sucker->call('method', 'zzz') === 'zzz');
        static::assertTrue($sucker->sandbox(
                function ($arg) use ($target, $self) {
                    $self::assertTrue($target === self::class);
                    return $arg;
                }, null, ['zzz']
            ) === 'zzz'
        );
    }

    public function test_run_action_in_parent_class()
    {
        $inst = new class () extends MyClass {
            private function method3()
            {
                return 'bay';
            }

            private static function method4()
            {
                return 'bay';
            }
        };
        $sucker = new Sucker($inst);
        static::assertTrue($sucker->call(MyClass::class . '::method3') === 'hello');

        $sucker = new Sucker(get_class($inst));
        static::assertTrue($sucker->call(MyClass::class . '::method4') === 'hello');
        $self = $this;
        static::assertTrue($sucker->sandbox(function () use ($self) {
                $self::assertTrue(MyClass::class === self::class);
                return self::method4();
            }, MyClass::class . '::') === 'hello');
    }
}