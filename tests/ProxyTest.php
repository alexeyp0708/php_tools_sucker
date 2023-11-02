<?php


namespace Alpa\Tools\Tests\Sucker;

use Alpa\Tools\Tests\Sucker\Fixtures;
use Alpa\Tools\Sucker\Proxy;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{
    public function test_handlers_to_object()
    {
        $target = new Fixtures\SubChildClass();
        $proxy = new Proxy($target);
        // isset
        $this->assertTrue(isset($proxy->private_prop));
        $this->assertTrue(isset($proxy->private_prop));
        $this->assertTrue(isset($proxy(Fixtures\ChildClass::class)->private_prop));
        $this->assertTrue(isset($proxy(Fixtures\CoreClass::class)->private_prop));
        $this->assertTrue(!isset($proxy(Fixtures\CoreClass::class)->none_prop));
        // get
        $this->assertTrue($proxy->private_prop === 'private_subchild_prop');
        $this->assertTrue($proxy->private_subchild_prop === 'private_subchild_prop');
        $this->assertTrue($proxy(Fixtures\ChildClass::class)->private_prop === 'private_child_prop');
        $this->assertTrue($proxy(Fixtures\ChildClass::class)->private_child_prop === 'private_child_prop');
        $this->assertTrue($proxy(Fixtures\CoreClass::class)->private_prop === 'private_core_prop');
        $this->assertTrue($proxy(Fixtures\CoreClass::class)->private_core_prop === 'private_core_prop');
        $this->assertTrue($proxy(Fixtures\CoreClass::class)->public_prop === 'public_subchild_prop');
        $this->assertTrue($proxy(Fixtures\ChildClass::class)->public_prop === 'public_subchild_prop');
        $this->assertTrue($proxy->public_prop === 'public_subchild_prop');
        //set
        $proxy(Fixtures\CoreClass::class)->private_prop = 'private_core_prop_changed';
        $this->assertTrue($target->getPrivateProp() === 'private_core_prop_changed');
        //call
        $this->assertTrue($proxy->private_method() === 'private_subchild_method');
        $this->assertTrue($proxy(Fixtures\ChildClass::class)->private_method() === 'private_child_method');
        $this->assertTrue($proxy(Fixtures\CoreClass::class)->private_method() === 'private_core_method');

        // unset
        unset($proxy(Fixtures\CoreClass::class)->private_prop);
        $this->assertTrue($target->getPrivateProp() === null);

        //iterator
        $expected = [
            'private_subchild_prop' => 'private_subchild_prop',
            'private_prop' => 'private_subchild_prop',
            'public_prop' => 'public_subchild_prop',
            'public_subchild_prop' => 'public_subchild_prop',
            'public_child_prop' => 'public_child_prop',
            'public_core_prop' => 'public_core_prop',
            'protected_prop' => 'protected_child_prop'

        ];
        $actual = [];
        foreach ($proxy as $key => $value) {
            $actual[$key] = $value;
        }
        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);

        $expected = [
            'private_core_prop' => 'private_core_prop',
            //'private_prop'=>'private_core_prop_changed', unset
            'public_prop' => 'public_subchild_prop',
            'public_subchild_prop' => 'public_subchild_prop',
            'public_child_prop' => 'public_child_prop',
            'public_core_prop' => 'public_core_prop',
            'protected_prop' => 'protected_child_prop'
        ];
        $actual = [];
        foreach ($proxy(Fixtures\CoreClass::class) as $key => $value) {
            $actual[$key] = $value;
        }
        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function test_handlers_to_class()
    {
        $target = Fixtures\SubChildClass::class;
        $proxy = new Proxy($target);
        // isset
        $this->assertTrue(isset($proxy->private_static_prop));
        $this->assertTrue(isset($proxy->private_static_prop));
        $this->assertTrue(isset($proxy(Fixtures\ChildClass::class)->private_static_prop));
        $this->assertTrue(isset($proxy(Fixtures\CoreClass::class)->private_static_prop));
        $this->assertTrue(!isset($proxy(Fixtures\CoreClass::class)->none_prop));
        // get
        $this->assertTrue($proxy->private_static_prop === 'private_static_subchild_prop');
        $this->assertTrue($proxy->private_static_subchild_prop === 'private_static_subchild_prop');
        $this->assertTrue($proxy(Fixtures\ChildClass::class)->private_static_prop === 'private_static_child_prop');
        $this->assertTrue($proxy(Fixtures\ChildClass::class)->private_static_child_prop === 'private_static_child_prop');
        $this->assertTrue($proxy(Fixtures\CoreClass::class)->private_static_prop === 'private_static_core_prop');
        $this->assertTrue($proxy(Fixtures\CoreClass::class)->private_static_core_prop === 'private_static_core_prop');
        $this->assertTrue($proxy(Fixtures\CoreClass::class)->public_static_prop === 'public_static_core_prop');
        $this->assertTrue($proxy(Fixtures\ChildClass::class)->public_static_prop === 'public_static_child_prop');
        $this->assertTrue($proxy->public_static_prop === 'public_static_subchild_prop');
        //set
        $buf = $proxy(Fixtures\CoreClass::class)->private_static_prop;
        $proxy(Fixtures\CoreClass::class)->private_static_prop = 'private_static_core_prop_changed';
        $this->assertTrue($target::getPrivateStaticProp() === 'private_static_core_prop_changed');
        $proxy(Fixtures\CoreClass::class)->private_static_prop = $buf;
        //call
        $this->assertTrue($proxy->private_static_method() === 'private_static_subchild_method');
        $this->assertTrue($proxy(Fixtures\ChildClass::class)->private_static_method() === 'private_static_child_method');
        $this->assertTrue($proxy(Fixtures\CoreClass::class)->private_static_method() === 'private_static_core_method');

        // unset
        //unset($proxy(Fixtures\CoreClass::class)->private_static_prop);
        //$this->assertTrue($target->getPrivateStaticProp() === null);

        //iterator
        $expected = [
            'private_static_subchild_prop' => 'private_static_subchild_prop',
            'private_static_prop' => 'private_static_subchild_prop',
            'public_static_prop' => 'public_static_subchild_prop',
            'public_static_subchild_prop' => 'public_static_subchild_prop',
            'public_static_child_prop' => 'public_static_child_prop',
            'public_static_core_prop' => 'public_static_core_prop',
            'protected_static_child_prop' => 'protected_static_child_prop',
            'protected_static_core_prop' => 'protected_static_core_prop',
            'protected_static_prop' => 'protected_static_child_prop'

        ];
        $actual = [];
        foreach ($proxy as $key => $value) {
            $actual[$key] = $value;
        }
        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);

        $expected = [
            'private_static_prop' => 'private_static_core_prop',
            'private_static_core_prop' => 'private_static_core_prop',
            'public_static_prop' => 'public_static_core_prop',
            'public_static_core_prop' => 'public_static_core_prop',
            'protected_static_core_prop' => 'protected_static_core_prop',
            'protected_static_prop' => 'protected_static_core_prop'

        ];
        $actual = [];
        foreach ($proxy(Fixtures\CoreClass::class) as $key => $value) {
            $actual[$key] = $value;
        }
        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);

        //var_dump(get_object_vars($proxy));
    }

    public function test_references()
    {
    }
}