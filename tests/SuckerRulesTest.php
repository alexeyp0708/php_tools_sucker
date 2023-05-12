<?php

namespace Alpa\Tools\Tests\Sucker;

use PHPUnit\Framework\TestCase;


class SuckerRulesTest extends TestCase
{
    public function test_rules()
    {
        $tester=$this;
        $call = function()use($tester){
            $this->prop2=static::class;
            $tester->assertTrue(get_class($this) !== self::class);
            $tester->assertTrue(static::class !== self::class);
            $tester->assertTrue(get_class($this) === static::class);
            $tester->assertTrue($this->prop===B::class);
            $tester->assertTrue($this->method() === 'A_'.B::class);
            $tester->assertTrue($this->re_method() === 'B_'.B::class);
            $tester->assertTrue($this->private_method() === 'A_'.B::class);
        };
        $target=new B;
        $call=$call->bindTo($target,A::class);
        $call();
        $this->assertSame($target->method2(),'A_'.B::class);
        $this->assertSame($target->method3(),'B_'.B::class);
        $target=new B;
        $this->assertSame($target->method2(),'A_'.A::class);
    }
    
    public function test_test()
    {
        $this->assertTrue(true);
    }
}

class A{
    protected $prop=__CLASS__;
    private $prop2=__CLASS__;
    protected function method(){
        return 'A_'.$this->prop;
    }
    protected function re_method(){
        return 'A_'.$this->prop;
    }
    private function private_method(){
        return 'A_'.$this->prop;
    }
    public function method2(){
        return 'A_'.$this->prop2;
    }
}
class B extends A{
    protected $prop=__CLASS__;
    private $prop2=__CLASS__;
    protected function re_method(){
        return 'B_'.$this->prop;
    }
    private function private_method(){
        return 'B_'.$this->prop;
    }
    public function method3()
    {
        return 'B_'.$this->prop2;    
    }
}