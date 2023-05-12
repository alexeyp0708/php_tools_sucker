﻿#Журнал Dev ветки. 
- добавить возможность добавления замыканий в обект Sucker, которые будут заменять замыкания по умолчанию (-)
- добавить возможность добавления замыканий через класс.(-)
- теcns Sucker (-/)
# Alpa\Tools\Sucker\Sucker
For unit testing. A sucker for classes and objects to call private methods.

The component provides access to private  properties of an object / class.

##Changes in versions
###Версионность.
x.x.x 
- первая цифра (основная версия) означает стабильную реализацию. Между версиями существует не совместимость.  
- вторая цифра (дополнительная версия) означает добавление нового функционала и его изменения.  
  Между версиями существет несовместимость в нововведениях. Но функционал и API основной версии сохраняется.
- третья цифра (корректировочная версия)  Между версиями существует совместимость. 
  Доработка придыдущей версии, работа над ошибками. Приведение функционала к стабильности.
- alpha beta relise - стадии тестирования  основной версии. 

### Измения
* 0.1.0
  - Passing arguments by reference and returning results by reference.
  - добавлены методы Sucker::apply и Sucker::setRef  
  - Due to passing variables by reference,The functionality of passing arguments via unpacking (operator ...) has been replaced with passing arguments in an array.
    Это справедливо для методов Sucker::run, Sucker::sbox, Sucker::sanbox, Sucker::apply,
    метод Sucker::run теперь protected. 
    

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
В таком случае нужно запомнить главные правило -
по умолчанию поведение исполнения методов и доступ к свойствам обьекта будет такое же как в    
  [\Closure->bindTo](https://www.php.net/manual/ru/closure.bindto.php)
Пример:
```php
<?php
class A{
    protected $prop=__CLASS__;
    protected function method(){
      return 'A_'.$this->prop;
    }
    protected function re_method(){
      return 'A_'.$this->prop;
    }
    private function private_method(){
      return 'A_'.$this->prop;
    }
}
class B extends A{
    protected $prop=__CLASS__;
    protected function re_method(){
      return 'B_'.$this->prop;
    }
    private function private_method(){
      return 'B_'.$this->prop;
    }
}
$call = function(){
    var_dump(get_class($this) !== self::class);
    var_dump(static::class !== self::class);
    var_dump(get_class($this) === static::class);
    var_dump($this->prop===B::class);
    var_dump($this->method() === 'A_'.B::class);
    var_dump($this->re_method() === 'B_'.B::class);
    var_dump($this->private_method() === 'A_'.B::class);
};
$target=new B;
$call=$call->bindTo($target,A::class);
$call();
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
$sucker=new \Alpa\Tools\Sucker\Sucker($target);
$sucker_static=new \Alpa\Tools\Sucker\Sucker($target2);
echo $sucker->get('prop');//  'hello'
// WARNING:
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
$result =\Alpa\Tools\Sucker\Sucker::sbox(function(...$args){

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
    use \Alpa\Tools\Sucker\TSucker;
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

# Alpa\Tools\Sucker\Proxy

The component provides access to private and protected members of an object / class through a proxy object.
In order to obtain private members of an object / class, the [Alpa\Tools\Sucker\Sucker](#alpatoolssuckersucker) component is used.
And for syntactic sugar, the [Alpa\Tools\ProxyObject](https://github.com/alexeyp0708/php_tools_proxy_object) component is used.

```php
<?php
class A{
	private $a=1;
	private function method(){
		return $this->a;
	}
}
class B extends A{
	private $a=2;
	public $b=2;
}
$obj=new B();
$proxy = new Alpa\Tools\Sucker\Proxy($obj);
echo $proxy->a;// 2
echo $proxy(A::class)->a; //1
$proxy(A::class)->a=11;
echo $proxy(A::class)->method();//11
echo isset($proxy->a);//true
echo isset($proxy(A::class)->a);//true
foreach($proxy as $key=>$value){
	// 'a'=>2
	// 'b'=>2
}
foreach($proxy(A::class) as $key=>$value){
	// 'a'=>11
	// 'b'=>2	
}
unset($proxy->a);
unset($proxy(A::class)->a);

```
Working with static properties
```php
<?php
class A{
	private static $a=1;
	private static function  method(){
		return self::$a;
	}
}
class B extends A{
	private static $a=2;
	public static $b=2;
}
$proxy = new Alpa\Tools\Sucker\Proxy(B::class);
echo $proxy->a;// 2
echo $proxy(A::class)->a; //1
$proxy(A::class)->a=11;
echo $proxy(A::class)->method();//11
echo isset($proxy->a);//true
echo isset($proxy(A::class)->a);//true
foreach($proxy as $key=>$value){
	// 'a'=>2
	// 'b'=>2
}
foreach($proxy(A::class) as $key=>$value){
	// 'a'=>11
	// 'b'=>2	
}
```

