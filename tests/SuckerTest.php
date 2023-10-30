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
        $this->assertSame('private_static_child_prop', $sucker->get('private_static_child_prop'));
        $this->assertSame('private_static_child_prop', $sucker->get('private_static_prop'));
        $this->assertSame('protected_static_child_prop', $sucker->get('protected_static_prop'));

        $this->assertSame('private_static_core_prop', $sucker->get(CoreClass::class . '::private_static_core_prop'));
        $this->assertSame('private_static_core_prop', $sucker->get(CoreClass::class . '::private_static_prop'));
        $this->assertSame('protected_static_core_prop', $sucker->get(CoreClass::class . '::protected_static_prop'));


    }

    public function test_get()
    {
        $target = new ChildClass();
        $sucker = new Sucker($target);

        $this->assertSame('private_child_prop', $sucker->get('private_child_prop'));
        $this->assertSame('private_child_prop', $sucker->get('private_prop'));
        $this->assertSame('protected_child_prop', $sucker->get('protected_prop'));

        $this->assertSame('private_core_prop', $sucker->get(CoreClass::class . '::private_core_prop'));
        $this->assertSame('private_core_prop', $sucker->get(CoreClass::class . '::private_prop'));

        // передача родительского класса не имеет значения
        $this->assertSame('protected_child_prop', $sucker->get(CoreClass::class . '::protected_prop'));
    }

    public function test_static_ref_get()
    {
        $target = ChildClass::class;
        $sucker = new Sucker($target);
        $ref_prop =& $sucker->get(CoreClass::class . '::private_static_prop');
        $ref_prop_val = $ref_prop;
        $ref_prop = 'changed';
        $this->assertSame('changed', $sucker->get(CoreClass::class . '::private_static_prop'));
        $this->assertSame('private_static_child_prop', $sucker->get('private_static_prop'));
        $ref_prop = $ref_prop_val;
        unset($ref_prop);
        $ref_prop =& $sucker->get('private_static_prop');
        $ref_prop_val = $ref_prop;
        $ref_prop = 'changed';
        $this->assertSame('changed', $sucker->get('private_static_prop'));
        $this->assertSame('private_static_core_prop', $sucker->get(CoreClass::class . '::private_static_prop'));
        $ref_prop = $ref_prop_val;
        unset($ref_prop);
        $this->assertSame('private_static_child_prop', $sucker->get('private_static_prop'));
    }

    public function test_ref_get()
    {
        $target = new ChildClass();
        $sucker = new Sucker($target);
        $ref_prop =& $sucker->get(CoreClass::class . '::private_prop');
        $ref_prop_val = $ref_prop;
        $ref_prop = 'changed';
        $this->assertSame('changed', $sucker->get(CoreClass::class . '::private_prop'));
        $this->assertSame('private_child_prop', $sucker->get('private_prop'));
        $ref_prop = $ref_prop_val;
        unset($ref_prop);
        $ref_prop =& $sucker->get('private_prop');
        $ref_prop_val = $ref_prop;
        $ref_prop = 'changed';
        $this->assertSame('changed', $sucker->get('private_prop'));
        $this->assertSame('private_core_prop', $sucker->get(CoreClass::class . '::private_prop'));
        $ref_prop = $ref_prop_val;
        unset($ref_prop);
        $this->assertSame('private_child_prop', $sucker->get('private_prop'));

        $check=false;
        set_error_handler(function(...$args) use (&$check){
            if(substr($args[1],0,18)==='Undefined property'){
                $check=true;
                return true;
            }
            return false;
        },E_USER_WARNING|E_USER_NOTICE);
        $sucker->get(CoreClass::class . '::protected_core_prop');
        restore_error_handler();
        $this->assertTrue($check,'Test for generating an error when a property is missing');
        $check=false;
        set_error_handler(function(...$args) use (&$check){
            if(substr($args[1],0,18)==='Undefined property'){
                $check=true;
                return true;
            }
            return false;
        },E_USER_WARNING|E_USER_NOTICE);
        $sucker->get(CoreClass::class . '::protected_core_prop');
        restore_error_handler();
        $this->assertTrue($check,'We check whether the property was not created after checking for absence');
    }

    public static function test_static_set()
    {
        $target = ChildClass::class;
        $sucker = new Sucker($target);
        $buf = $sucker->get('private_static_child_prop');
        $sucker->set('private_static_child_prop', 'changed');
        self::assertSame('changed', $sucker->get('private_static_child_prop'));
        $sucker->set('private_static_child_prop', $buf);
        self::assertSame($buf, $sucker->get('private_static_child_prop'));

        $buf = $sucker->get('protected_static_prop');
        $sucker->set('protected_static_prop', 'changed');
        self::assertSame('changed', $sucker->get('protected_static_prop'));
        $sucker->set('protected_static_prop', $buf);
        self::assertSame($buf, $sucker->get('protected_static_prop'));
        
        $buf = $sucker->get(CoreClass::class . '::private_static_core_prop');
        $sucker->set(CoreClass::class . '::private_static_core_prop', 'changed');
        self::assertSame('changed', $sucker->get(CoreClass::class . '::private_static_core_prop'));
        $sucker->set(CoreClass::class . '::private_static_core_prop', $buf);
        self::assertSame($buf, $sucker->get(CoreClass::class . '::private_static_core_prop'));

        $buf = $sucker->get(CoreClass::class . '::protected_static_prop');
        $sucker->set(CoreClass::class . '::protected_static_prop', 'changed');
        self::assertSame('changed', $sucker->get(CoreClass::class . '::protected_static_prop'));
        $sucker->set(CoreClass::class . '::protected_static_prop', $buf);
        self::assertSame($buf, $sucker->get(CoreClass::class . '::protected_static_prop'));

        $buf = $sucker->get(CoreClass::class . '::protected_static_core_prop');
        $sucker->set(CoreClass::class . '::protected_static_core_prop', 'changed');
        self::assertSame('changed', $sucker->get(CoreClass::class . '::protected_static_core_prop'));
        self::assertSame('changed', $sucker->get('protected_static_core_prop'));
        $sucker->set(CoreClass::class . '::protected_static_core_prop', $buf);
        self::assertSame($buf, $sucker->get(CoreClass::class . '::protected_static_core_prop'));
    }

    public function test_set()
    {
        $target = new ChildClass;
        $sucker = new Sucker($target);
        $buf = $sucker->get('private_child_prop');
        $sucker->set('private_child_prop', 'changed');
        $this->assertSame('changed', $sucker->get('private_child_prop'));
        $sucker->set('private_child_prop', $buf);
        $this->assertSame($buf, $sucker->get('private_child_prop'));

        $buf = $sucker->get('protected_prop');
        $sucker->set('protected_prop', 'changed');
        $this->assertSame('changed', $sucker->get('protected_prop'));
        $sucker->set('protected_prop', $buf);
        $this->assertSame($buf, $sucker->get('protected_prop'));

        $buf = $sucker->get(CoreClass::class . '::private_core_prop');
        $sucker->set(CoreClass::class . '::private_core_prop', 'changed');
        $this->assertSame('changed', $sucker->get(CoreClass::class . '::private_core_prop'));
        $sucker->set(CoreClass::class . '::private_core_prop', $buf);
        $this->assertSame($buf, $sucker->get(CoreClass::class . '::private_core_prop'));

        $buf = $sucker->get(CoreClass::class . '::protected_prop');
        $sucker->set(CoreClass::class . '::protected_prop', 'changed');
        $this->assertSame('changed', $sucker->get(CoreClass::class . '::protected_prop'));
        $this->assertSame('changed', $sucker->get('protected_prop'));
        $sucker->set(CoreClass::class . '::protected_prop', $buf);
        $this->assertSame($buf, $sucker->get(CoreClass::class . '::protected_prop'));

        set_error_handler(function(...$args) use (&$check){
            var_dump($args[1]);
        },E_DEPRECATED);
        restore_error_handler();
        
        // As of PHP 8.2, adding dynamic properties is deprecated
        //  deprecated start
        //????
        $sucker->set(CoreClass::class . '::protected_core_prop', 'changed'); 
       
        $this->assertSame('changed', $sucker->get(CoreClass::class . '::protected_core_prop'));
        $this->assertSame('changed', $sucker->get('protected_core_prop'));
        $sucker->unset(CoreClass::class . '::protected_core_prop');
        //$sucker->set(CoreClass::class . '::protected_core_prop', $buf);
        //$this->assertSame($buf, $sucker->get(CoreClass::class . '::protected_core_prop'));
        //  deprecated end
    }

    public static function test_static_setRef()
    {
        $target = ChildClass::class;
        $sucker = new Sucker($target);
        // reference test
        $var = 'changed';
        $buf = $sucker->get(CoreClass::class . '::protected_static_prop');
        $sucker->setRef(CoreClass::class . '::protected_static_prop', $var);
        self::assertSame('changed', $sucker->get(CoreClass::class . '::protected_static_prop'));
        self::assertSame('protected_static_child_prop', $sucker->get('protected_static_prop'));
        $var = $buf;
        self::assertSame($buf, $sucker->get(CoreClass::class . '::protected_static_prop'));
        //$var2=&$var;
        unset($var);
        //xdebug_debug_zval('var2');
    }

    public function test_setRef()
    {
        $target = new ChildClass;
        $sucker = new Sucker($target);
        // reference test
        $var = 'changed';
        $buf = $sucker->get(CoreClass::class . '::protected_prop');
        $sucker->setRef(CoreClass::class . '::protected_prop', $var);
        self::assertSame('changed', $sucker->get(CoreClass::class . '::protected_prop'));
        self::assertSame('changed', $sucker->get('protected_prop'));
        $var = $buf;
        self::assertSame($buf, $sucker->get(CoreClass::class . '::protected_prop'));
        //$var2=&$var;
        unset($var);
        //xdebug_debug_zval('var2');
    }

    public static function test_static_call()
    {
        $target = ChildClass::class;
        $sucker = new Sucker($target);
        self::assertSame('private_static_child_method', $sucker->call('private_static_child_method'));
        self::assertSame('private_static_child_method', $sucker->call('private_static_method'));
        self::assertSame('protected_static_child_method', $sucker->call('protected_static_method'));
        self::assertSame(['hello', 'friend'], $sucker->call('static_returns_args', 'hello', 'friend'));

        self::assertSame('private_static_core_method', $sucker->call(CoreClass::class . '::private_static_core_method'));
        self::assertSame('private_static_core_method', $sucker->call(CoreClass::class . '::private_static_method'));
        self::assertSame('protected_static_core_method', $sucker->call(CoreClass::class . '::protected_static_method'));
    }

    public function test_call()
    {
        $target = new ChildClass;
        $sucker = new Sucker($target);
        $this->assertSame('private_child_method',$sucker->call('private_child_method') );
        $this->assertSame( 'private_child_method',$sucker->call('private_method'));
        $this->assertSame('protected_child_method',$sucker->call('protected_method'));
        $this->assertSame(['hello', 'friend'],$sucker->call('returns_args', 'hello', 'friend') );

        $this->assertSame('private_core_method',$sucker->call(CoreClass::class . '::private_core_method') );
        $this->assertSame('private_core_method',$sucker->call(CoreClass::class . '::private_method') );
        $this->assertSame('protected_child_method',$sucker->call(CoreClass::class . '::protected_method') );
    }

    public function test_ref_apply()
    {
        $arg = 'hello';
        $target = new ChildClass;
        $sucker = new Sucker($target);
        $answer =& $sucker->apply('ref_method', [&$arg]);
        $arg = 'bay';
        $this->assertSame($answer, $arg);
        unset($answer);
        //xdebug_debug_zval('arg');
        unset($arg);
        $arg = 'hello';
        $arg2 = 'bay';
        $answer =& $sucker->apply('return_args_method', [&$arg, &$arg2]);
        $arg = 'qwer';
        $this->assertSame($arg,$answer[0] );
        $arg2 = 'qwer2';
        $this->assertSame($arg2,$answer[1] );
        unset($answer[0], $answer[1], $arg, $arg1);
    }

    public static function test_static_ref_apply()
    {
        $arg = 'hello';
        $target = new ChildClass;
        $sucker = new Sucker($target);
        $answer =& $sucker->apply('ref_method', [&$arg]);
        $arg = 'bay';
        self::assertSame( $answer,$arg);
        unset($answer);
        //xdebug_debug_zval('arg');
        unset($arg);
    }


    public function test_isset()
    {
        $target = new ChildClass;
        $sucker = new Sucker($target);
        $this->assertTrue($sucker->isset('private_child_prop'));
        $this->assertTrue(!$sucker->isset('private_core_prop'));
        $this->assertTrue(!$sucker->isset('no_prop'));

        $this->assertTrue(!$sucker->isset(CoreClass::class . '::private_child_prop'));
        $this->assertTrue($sucker->isset(CoreClass::class . '::private_core_prop'));
        $this->assertTrue(!$sucker->isset(CoreClass::class . '::no_prop'));
    }

    public static function test_static_isset()
    {
        $target = ChildClass::class;
        $sucker = new Sucker($target);
        self::assertTrue($sucker->isset('private_static_child_prop'));
        self::assertTrue(!$sucker->isset('private_static_core_prop'));
        self::assertTrue(!$sucker->isset('no_prop'));

        self::assertTrue(!$sucker->isset(CoreClass::class . '::private_static_child_prop'));
        self::assertTrue($sucker->isset(CoreClass::class . '::private_static_core_prop'));
        self::assertTrue(!$sucker->isset(CoreClass::class . '::no_prop'));
    }

    public function test_unset()
    {
        $target = new ChildClass;
        $sucker = new Sucker($target);
        $sucker->unset('private_child_prop');
        $this->assertTrue(!$sucker->isset('private_child_prop'));
    }

    public static function test_static_unset()
    {
        $target = ChildClass::class;
        $sucker = new Sucker($target);
        $check = false;
        try {
            $sucker->unset('private_static_child_prop');
        } catch (\Error $e) {
            $check = true;
        } finally {
            self::assertTrue($check);
        }
        restore_error_handler();
    }

    public function test_each()
    {
        $target = new ChildClass;
        $sucker = new Sucker($target);
        $tester = $this;
        $keys = [];
        $callThis = null;
        $callClass = null;
        $func = function ($key, &$value) use ($tester, $target, &$callThis, &$callClass, &$keys) {
            $keys[$key] = &$value;
            if (is_null($callThis)) {
                $callThis = $this;
            } else if ($callThis !== $this) {
                $tester->assertTrue(false);
            }
            if (is_null($callClass)) {
                $callClass = self::class;
            } else if ($callClass !== self::class) {
                $tester->assertTrue(false);
            }
        };
        $sucker->each($func);
        $expectedKeys = [
            'private_child_prop' => 'private_child_prop',
            'private_prop' => 'private_child_prop',
            'public_prop' => 'public_child_prop',
            'protected_prop' => 'protected_child_prop',
            'public_child_prop' => 'public_child_prop',
            'public_core_prop' => 'public_core_prop'
        ];
        $this->assertSame($expectedKeys, $keys);
        $this->assertTrue($callThis === $target);
        $this->assertSame($callClass, get_class($target));
        // reference test
        $buf = $keys['private_prop'];
        $keys['private_prop'] = 'changed';
        $this->assertSame($keys['private_prop'], $sucker->get('private_prop'));
        $keys['private_prop'] = $buf;
        $this->assertSame($buf, $sucker->get('private_prop'));
        //end reference test
        foreach ($keys as $key => $value) {
            unset($keys[$key]);
        }
        $callThis = null;
        $callClass = null;
        $sucker->each($func, CoreClass::class);
        $actualKeys = [
            'public_prop' => 'public_child_prop',
            'protected_prop' => 'protected_child_prop',
            'public_child_prop' => 'public_child_prop',
            'private_core_prop' => 'private_core_prop',
            'public_core_prop' => 'public_core_prop',
            'private_prop' => 'private_core_prop'
        ];
        $this->assertSame($actualKeys, $keys);
        $this->assertTrue($callThis === $target);
        $this->assertSame(CoreClass::class,$callClass );

        //reference test
        $buf = $keys['private_prop'];
        $keys['private_prop'] = 'changed';
        $this->assertSame($keys['private_prop'], $sucker->get(CoreClass::class . '::private_prop'));
        $this->assertSame('private_child_prop',$sucker->get('private_prop') );
        $keys['private_prop'] = $buf;
        $this->assertSame( $buf,$sucker->get(CoreClass::class . '::private_prop'));
        //end reference test
    }

    public static function test_static_each()
    {
        $target = ChildClass::class;
        $sucker = new Sucker($target);
        $tester = self::class;
        $keys = [];
        $callThis = null;
        $callClass = null;
        $func = function ($key, &$value) use ($tester, $target, &$callClass, &$keys) {
            $keys[$key] = &$value;
            if (is_null($callClass)) {
                $callClass = self::class;
            } else if ($callClass !== self::class) {
                $tester::assertTrue(false);
            }
        };
        $sucker->each($func);
        $actualKeys = [
            'public_static_prop' => 'public_static_child_prop',
            'protected_static_prop' => 'protected_static_child_prop',
            'public_static_child_prop' => 'public_static_child_prop',
            'protected_static_child_prop' => 'protected_static_child_prop',
            'private_static_prop' => 'private_static_child_prop',
            'private_static_child_prop' => 'private_static_child_prop',
            'protected_static_core_prop' => 'protected_static_core_prop',
            'public_static_core_prop' => 'public_static_core_prop'

        ];
        self::assertEquals($actualKeys,$keys );
        self::assertSame($target,$callClass );
        // reference test
        $buf = $keys['private_static_prop'];
        $keys['private_static_prop'] = 'changed';
        self::assertSame( $keys['private_static_prop'],$sucker->get('private_static_prop'));
        $keys['private_static_prop'] = $buf;
        self::assertSame($buf,$sucker->get('private_static_prop') );
        //end reference test
        foreach ($keys as $key => $value) {
            unset($keys[$key]);
        }
        $callThis = null;
        $callClass = null;
        $sucker->each($func, CoreClass::class);
        $actualKeys = [
            'public_static_prop' => 'public_static_core_prop',
            'protected_static_prop' => 'protected_static_core_prop',
            'protected_static_core_prop' => 'protected_static_core_prop',
            'private_static_prop' => 'private_static_core_prop',
            'private_static_core_prop' => 'private_static_core_prop',
            'public_static_core_prop' => 'public_static_core_prop'
        ];
        self::assertEquals($actualKeys,$keys );
        self::assertSame(CoreClass::class,$callClass );

        //reference test
        $buf = $keys['private_static_prop'];
        $keys['private_static_prop'] = 'changed';
        self::assertSame($keys['private_static_prop'],$sucker->get(CoreClass::class . '::private_static_prop') );
        self::assertSame('private_static_child_prop',$sucker->get('private_static_prop') );
        $keys['private_static_prop'] = $buf;
        self::assertSame($buf,$sucker->get(CoreClass::class . '::private_static_prop') );
        //end reference test
    }

    public function test_sandbox()
    {
        $target = new ChildClass;
        $sucker = new Sucker($target);
        $var = 'qwer';
        $tester = $this;
        $selfClass = get_class($target);
        $func = function ($a, $b, &$c) use ($tester, $target, &$selfClass) {
            $tester->assertSame('hello',$a );
            $tester->assertSame('friend', $b);
            $tester->assertTrue($this === $target);
            $tester->assertTrue(self::class === $selfClass);
            $c = 'rewq';
        };
        unset($var);
        $var = 'qwer';
        $sucker->sandbox($func, null, ['hello', 'friend', &$var]);
        $tester->assertSame('rewq',$var );
        $selfClass = CoreClass::class;
        $sucker->sandbox($func, CoreClass::class, ['hello', 'friend', &$var]);
        $tester->assertSame('rewq',$var );
    }
    public function test_sandbox_by_reference(){}
    
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
        static::assertSame($sucker->call('method', 'zzz'), 'zzz');
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