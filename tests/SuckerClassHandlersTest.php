<?php


namespace Alpa\Tools\Tests\Sucker;

use Alpa\Tools\Sucker\SuckerClassHandlers;

use Alpa\Tools\Tests\Sucker\SuckerObjectHandlersTest;

class SuckerClassHandlersTest extends \PHPUnit\Framework\TestCase
{
    private static array $fixtures = [];
    
    public function setUp():void
    {

    }
    public static function setUpBeforeClass():void
    {
        $handlers = new SuckerClassHandlers();
        $handlers->setSubject(Fixtures\SubChildClass::class);
        self::$fixtures['handlers'] = $handlers;
    }
    public static function providerByScopes():array
    {
        return [
            [Fixtures\SubChildClass::class],
            [Fixtures\ChildClass::class],
            [Fixtures\CoreClass::class],
        ];
    }
    public static function providerByProperties(): array
    {
        
        unset($value);
        return [
            // test current class object properties 
            //Scope SubChildClass class
            ['static_private_subchild_prop', null, 'static_private_subchild_prop'],//0
            ['static_private_prop', null, 'static_private_subchild_prop'],//1
            ['static_public_prop', null, 'static_public_subchild_prop'],//2
            ['static_public_subchild_prop', null, 'static_public_subchild_prop'],//3
            ['static_protected_prop', null, 'static_protected_child_prop'],//4
            ['static_public_child_prop', null, 'static_public_child_prop'],//5
            ['static_public_core_prop', null, 'static_public_core_prop'],//6
            // test parents  object properties
            // Scope ChildClass class
            ['static_public_prop', Fixtures\ChildClass::class, 'static_public_child_prop'],//7
            ['static_public_child_prop', Fixtures\ChildClass::class, 'static_public_child_prop'],//7
            ['static_public_core_prop', Fixtures\ChildClass::class, 'static_public_core_prop'],//7
            // own ChildClass class
            ['static_private_child_prop', Fixtures\ChildClass::class, 'static_private_child_prop'],//8
            ['static_private_prop', Fixtures\ChildClass::class, 'static_private_child_prop'],//9
            ['static_protected_prop', Fixtures\ChildClass::class, 'static_protected_child_prop'],//10
            //Scope CoreClass class
            ['static_private_core_prop', Fixtures\CoreClass::class, 'static_private_core_prop'],//11
            ['static_private_prop', Fixtures\CoreClass::class, 'static_private_core_prop'],//12
            ['static_public_core_prop', Fixtures\CoreClass::class, 'static_public_core_prop'],//13
            ['static_protected_prop', Fixtures\CoreClass::class, 'static_protected_core_prop'],//14
            ['static_public_prop', Fixtures\CoreClass::class, 'static_public_core_prop'],//15
            ['static_public_core_prop', Fixtures\CoreClass::class, 'static_public_core_prop'],//16
            ['static_public_prop', Fixtures\CoreClass::class, 'static_public_core_prop'],//17
        ];
    }

/*    \/**
     * @dataProvider providerForGet
     *\/
    public function test_get(?string $property, ?string $scope = null, $expected='')
    {
        $handlers = self::$fixtures['handlers'];
        $this->assertSame($expected, $handlers->setScope($scope)->get($property));
    }*/
    /**
     * @dataProvider providerByProperties
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

    /**
     * @dataProvider providerByProperties
     */
    public function test_setByReference(string $property, ?string $scope = null, $expected='')
    {
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope($scope);
        $restore = $handlers->get($property);
        $value='changed';
        $handlers->set($property,$value);
        $this->assertSame($value,$handlers->get($property));
        $value=$restore;
        $this->assertSame($expected, $handlers->get($property));
        unset($var);
    }

    public static function providerByMethods(): array
    {
        return [
            ['static_private_subchild_method',null,'static_private_subchild_method'],//0
            ['static_private_method',null,'static_private_subchild_method'],//1
            ['static_protected_method',null,'static_protected_child_method'],//2
            
            ['static_private_child_method',Fixtures\ChildClass::class,'static_private_child_method'],//3
            ['static_private_method',Fixtures\ChildClass::class,'static_private_child_method'],//4
            ['static_protected_method',Fixtures\ChildClass::class,'static_protected_child_method'],//5
            
            
            ['static_private_core_method',Fixtures\CoreClass::class,'static_private_core_method'],//6
            ['static_private_method',Fixtures\CoreClass::class,'static_private_core_method'],//7
        ];
    }

    /**
     *  @dataProvider providerByMethods
     */
    public function test_call(string $method,?string $scope,$expected)
    {
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope($scope);
        $this->assertSame($expected,$handlers->call($method));
    }

    public function test_callByReference()
    {
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope(Fixtures\CoreClass::class);
        $var1='hello';
        $var2=&$handlers->call('static_testReference',$var1);
        $var2='bay';
        $this->assertSame($var2,$var1);
    }

    /**
     * @dataProvider providerByScopes
     */
    public function test_each($scope)
    {
        $expected=[$scope=>[]];
        foreach(self::providerByProperties() as $value){
            if($scope===($value[1]??Fixtures\SubChildClass::class)){
                $expected[$scope][$value[0]]=$value[2];
            }
        }
        $tester=$this;
        $handlers = self::$fixtures['handlers'];
        
        $handlers->setScope($scope);
        $check=0;
        $handlers->each(function($key,$value) use (&$check,$tester,$scope){
            $tester->assertSame($scope,self::class);
            if($check===0){
                $check++;
            }
            return true;//break 
        });
        $tester->assertTrue(1===$check,'Test break foreach (return true)');
        $handlers->each(function($key,$value) use ($expected,$tester,$scope){
            /** @deprecated  */
            if(substr($key,0,7)!=='static_'){return;}
            $tester->assertSame($scope,self::class);
            $tester->assertSame($expected[self::class][$key],$value);
        });
    }

    public function test_eachByReference()
    {
        $tester=$this;
        $handlers = self::$fixtures['handlers'];
        $handlers->setScope(null);
        $handlers->each(function($key,&$value) use ($tester,$handlers){
            $buf=$value;
            $value='changed';
            $tester->assertSame($value,$handlers->get($key));
            $value=$buf;
            $tester->assertSame($value,$handlers->get($key));
            return true;
        });
    }

}