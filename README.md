# Alpa\Tools\Sucker
For unit testing. A sucker for classes and objects to call private methods.

The component provides access to private  properties of an object / class.

## Implementation basis

Component Implements the following concept of accessing private properties :

```php
//Example
class Helper{
     public static function sandbox(\Closure $call,$target,?string $slaveClass=null,...$args)
     {
         $slaveClass=!empty($slaveClass)?$slaveClass:(is_string($target)?$target:get_class($target));
         $target=!is_string($target)?$target:null;
         $call=$call->bindTo($target,$slaveClass);
         return $call(...$args);
     }
}
class A{
    private $prop=1;
}
class B extends A{}
$b=new B;
Helper::sanbox(function(...$args){
    return $this->prop;
},$b,A::class,'argument 1');
```

## Getting started

```php
<?php
class A{
    private $prop='bay';
    private static $static_prop='bay';
    private function method(){
        return 'bay';
    }
};
class B extends A{
    private $prop='hello';
    private static $static_prop='hello';
     private function method(){
        return 'hello';
    }
};
$target=new B();
$target2=B::class;
$sucker=new \Alpa\Tools\UnitTest\Sucker\Sucker($target);
$sucker_static=new \Alpa\Tools\UnitTest\Sucker\Sucker($target2);
echo $sucker->get('prop');//  'hello'
echo $sucker->get('A::prop');// 'bay'
echo $sucker_static->get('static_prop');// return 'hello'
echo $sucker_static->get('A::static_prop');// return 'bay'

echo $sucker->call('method');// 'hello' 
echo $sucker->call('A::method');// 'bay' 

$value='other hello';
$sucker->set('prop',$value);// void;
echo $sucker->isset('prop'); // true;
$sucker->unset('prop'); // void;
$sucker->each(function($key,$value){
    echo $key .' => '.$value; // prop => hello
}); // void;
$sucker->each(function($key,$value){
    echo $key .' => '.$value; // prop => bay
},'A'); // void;

$result= $sucker->sandbox(function(...$args){
    // $args===['Hello','Bay'];
    // $this===$target
    // self::class === B::class //get_class($target);
    //your code
    // return your result
},null,'Hello','Bay');

$result= $sucker->sandbox(function(...$args){
    // $args===['Hello','Bay'];
    // $this===$target
    // $this surrounded by A class
    // self::class === A::class;
    // your code
    // return your result
},'A','Hello','Bay');
or 
$result =\Alpa\Tools\UnitTest\Sucker\Sucker::sbox(function(...$args){

},$target,A::class,'arg1','arg2');
```

### Use trait

Using the trait is convenient because the sucker functionality can be used through the target object itself. In this
case, the target object becomes callable. It is syntactic sugar. If you need to test the private properties of a class,
then for this create a fixture of the child class extending the testing class and add the
trait `\Alpa\Tools\UnitTest\Sucker\TSucker`.

```php
<?php

class A{
    private $prop='bay';
   
}
class B extends A{
    use \Alpa\Tools\UnitTest\Sucker\TSucker;
       protected $prop='hello';
};
$inst=new B(); 
$propResult=$inst('prop');// hello
$propResult=$inst('prop','get');// hello
$propResult=$inst('A::prop','get');// bay
$inst('A::prop','set','other result');
$propResult=$inst('prop');// hello
//$propResult=$inst('A::prop','other result');// bay
```



