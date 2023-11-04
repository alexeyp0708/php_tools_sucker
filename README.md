# Sucker

For unit testing. Sucker to classes and objects for accessing private methods and properties.

The component provides access to private properties of an object / class.

## Install

`composer require alpa/tools_sucker:1.0.*`

## Preface

Let's create classes for examples.

```php
<?php 
class A{
    private $private_prop='hello';
    private static $static_private_prop='hello';
    private function private_method($arg)
    {
        return $arg;
    }
    private function & private_methodByReference(&$arg=null)
    {
        return $arg;
    }
}

class B extends A{
    private $private_prop='bay';
    private static $static_private_prop='bay';
    private function private_method($arg)
    {
        return strtoupper($arg);
    }
}
```

### syntactic sugar

There are 3 ways (API) to use the component.

1 way - if you need efficiency and a minimum of executable code under the hood.

```php
use A;
use B;
use Alpa\Tools\Sucker\SuckerObjectHandlers;
use Alpa\Tools\Sucker\SuckerClassHandlers;

$handler=new SuckerObjectHandlers(new B);
//scope B class
echo $handler->get('private_prop');// bay
echo "\n";
// scope A class;
echo $handler->setScope(A::class)->get('private_prop');// hello
echo "\n";
$handler->setScope(null);// reset scope

$staticHandlers=new SuckerClassHandlers(B::class);
echo $staticHandlers->get('static_private_prop');// hello
echo "\n";
echo $handler->setScope(A::class)->get('static_private_prop');// hello
echo "\n";
// environment maintained at A class level
echo $handler->get('static_private_prop');// hello
$handler->setScope(null); // reset scope
```

2 way - Wrapper pattern. Improves the previous solution.

```php
use A;
use B;
use Alpa\Tools\Sucker\Sucker;
$sucker=new Sucker(new B);
echo $sucker->get('private_prop');// bay
echo "\n";
echo $sucker(A::class)->get('private_prop');// hello  
echo "\n";
// Warn : The Scope is automatically reset after calling methods that are responsible for accessing members of the observable object
echo $sucker->get('private_prop');// bay
echo "\n";

$sucker=new Sucker(B::class);
echo $sucker->get('static_private_prop');// bay
echo "\n";
echo $sucker(A::class)->get('static_private_prop');// hello  
echo "\n";
// Warn : The Scope is automatically reset after calling methods that are responsible for accessing members of the observable object
echo $sucker->get('static_private_prop');// bay
echo "\n";
```

3 way - Proxy. syntactic sugar.

```php
use A;
use B;
use Alpa\Tools\Sucker\Proxy;
$proxy=new Proxy(new B);
echo $proxy->private_prop;// bay
echo $proxy(A::class)->private_prop;// hello  
// Warn : The Scope is automatically reset after calling methods that are responsible for accessing members of the observable object
echo $proxy->private_prop;// bay

$proxy=new Proxy(B::class);
echo $proxy->static_private_prop;// bay
echo $proxy(A::class)->static_private_prop;// hello  
// Warn : The Scope is automatically reset after calling methods that are responsible for accessing members of the observable object
echo $proxy->static_private_prop;// bay
```

# Basic principle

The code implements the following principle:

```php
<?php
Class A
{
  private $prop='hello';
}
$call=function(){
  // var_dump($this);//$target
  // var_dump(self::class);//$slaveClass
  return $this->prop;
  
};
$target= new A();
$slaveClass=A::class;
$call=$call->bindTo($target,$slaveClass);
echo $call();
```
## API Sucker class
```php
<?php

use Alpa\Tools\Sucker\Sucker;

$sucker = new Socker($object); // for members object
// or
$sucker=new Sucker(Target::class); // for static members class

//  при смене области видимости, обьект один и тот же (а не создается новый)
$sucker===$sucker(A::class);  
// сброc область видимости
$sucker(null); 
/* Внимание: область видимости сохраняется до первого вызова метода API 
и после сбрасывается на область видимости по умолчанию (класс обьекта).*/

/* & - указывает что аргументы нужно передавать по ссылке (в аргументах указан только для наглядности), 
или можно получать результат по ссылке. */
// запросить значение  свойства обькта/класса (можно по сыслке)
& $sucker->get( $prop ); & $sucker( SCOPE::class )->get( $prop );

// установить значение для свойства обькта/класса 
$sucker->set( $prop,  $value ); $sucker(SCOPE::class)->set( $prop, $value );

// установить значение для свойства обькта/класса по ссылке
$sucker->setRef( $prop, & $value ); $socker(SCOPE::class)->setRef( $prop, & $value );

// проверить наличие свойства обькта/класса 
$sucker->isset( $prop ); $sucker(SCOPE::class)->isset( $prop );

// удалить свойство из обьекта. для статического свойства класса будет вызвана стандартная ошибка. 
$sucker->unset( $prop ); $sucker(SCOPE::class)->unset( $prop );

// перебрать свойства объекта/класса.
/* in each closure  => self::class===SCOPE::class and opening $this */
$sucker->each(function ( $key, & $value ){return true;/* break*/}); $sucker( SCOPE::class )->each( function ($key, & $value){return true;/*break*/} );

// вызвать метод объекта/класса.
& $sucker->call( $method, ...$args ); & $sucker( SCOPE::class )->call( $prop ,...$args );

// вызвать метод объекта/класса с возможностьбю передачи аргументов по ссылке.
& $sucker->apply( $method, [& $arg,...] ); & $sucker( SCOPE::class )->apply( $method, [& $arg,...] ); 

// Песочница для обработки обьекта по своему усмотрению
/* in sandbox closure  => self::class===SCOPE::class and opening $this */
& $sucker->sandbox( function & (& $arg){},[ & $arg,...] ); & $sucker( SCOPE::class )->sandbox( function & (& $arg){},[ & $arg,...] );
 
```

## API Proxy class

```php
<?php

use Alpa\Tools\Sucker\Proxy;

$proxy = new Proxy($object); // for members object
// or
$proxy=new Sucker(Target::class); // for static members class

// при смене области видимости, обьект один и тот же (а не создается новый)
$proxy===$proxy(A::class);  
// сброc область видимости
$proxy(null); 
/* Внимание: область видимости сохраняется до первого вызова метода API 
и после сбрасывается на область видимости по умолчанию (класс обьекта).*/

// запросить значение  свойства обькта/класса (можно по сыслке)
& $proxy->prop; & $proxy(SCOPE::class)->prop;

//  установить значение для свойства обькта/класса 
$proxy->prop=$vslue;  $proxy(SCOPE::class)->prop=$value;

// проверить наличие свойства обькта/класса 
isset($proxy->prop); isset($proxy(SCOPE::class)->prop); 

// удалить свойство из обьекта. для статического свойства класса будет вызвана стандартная ошибка. 
unset($proxy->prop); unset($proxy(SCOPE::class)->prop); 

// перебрать свойства объекта/класса.
foreach ($proxy as $key=>$value){
  
}
foreach ($proxy(SCOPE::class) as $key=>$value){
  
}

// вызвать метод объекта/класса.
& $proxy->method(...$args);  & $proxy(SCOPE::class)->method(...$args);

/* Песочница для обработки обьекта по своему усмотрению.
Аргументы можно передавать по ссылке, а также результат получать по ссылке*.
/* in sandbox closure  => self::class===SCOPE::class and opening $this */
& $proxy(function & (...$args){},[& $arg,...]); & $proxy(SCOPE::class)(function & (...$args){},[ & $arg,...]);

```
## Get value  properties

```php
<?php 

use Alpa\Tools\Sucker\SuckerObjectHandlers;
use Alpa\Tools\Sucker\Sucker;
use Alpa\Tools\Sucker\Proxy;

$handler=new SuckerObjectHandlers(new B);
echo $handler->get('private_prop');// bay
echo $handler->setScope(A::class)->get('private_prop');// hello
//get by reference
$var = & $handler->get('private_prop');// & return A::$private_prop ==='hello'
$handler->setScope(null);

//or
$sucker=new Sucker(new B);
echo $sucker->get('private_prop');// bay
echo $sucker(A::class)->get('private_prop');// hello
//get by reference
$var = & $sucker(A::class)->get('private_prop');// & return A::$private_prop ==='hello'

//or 
$proxy=new Proxy(new B);
echo $proxy->private_prop;// bay
echo $proxy(A::class)->private_prop;// hello
//get by reference
$var = & $proxy(A::class)->private_prop;// & return A::$private_prop ==='hello'
```

## Set value to properties.

```php
<?php 

use Alpa\Tools\Sucker\SuckerObjectHandlers;
use Alpa\Tools\Sucker\Sucker;
use Alpa\Tools\Sucker\Proxy;


$handler=new SuckerObjectHandlers(new B);

//the value is passed by reference. Therefore it is necessary to pass it through a variable
$var='BAY'; // set by reference
$handler->set('private_prop',$var); //B::$private_prop=$var=>'BAY';
$handler->setScope(A::class)->set('private_prop',$var); //A::$private_prop=$var=>'BAY';
$handler->setScope(null);

//or
$sucker=new Sucker(new B);
$sucker->set('private_prop','HELLO');//B::$private_prop=$var=>'HELLO';
$sucker(A::class)->set('private_prop','HELLO');// //A::$private_prop=$var=>'HELLO';
//get by reference
$var='HELLO';
$sucker(A::class)->setRef('private_prop',$var);// A::$private_prop = &$var=>'HELLO'

//or 
$proxy=new Proxy(new B);
$proxy->private_prop='HELLO';// B::$private_prop='HELLO'
$proxy(A::class)->private_prop='HELLO';// A::$private_prop='HELLO'

//With a proxy, transmission by reference is not possible
```

## Check (isset)  property

```php

use Alpa\Tools\Sucker\SuckerObjectHandlers;
use Alpa\Tools\Sucker\Sucker;
use Alpa\Tools\Sucker\Proxy;


$handler=new SuckerObjectHandlers(new B);

echo $handler->isset('private_prop'); //check $obj(B)::$private_prop;
echo $handler->setScope(A::class)->isset('private_prop'); //check $obj(A)::$private_prop;
$handler->setScope(null);
//or
$sucker=new Sucker(new B);
$sucker->isset('private_prop');//check $obj(B)::$private_prop
$sucker(A::class)->isset('private_prop');// check $obj(A)::$private_prop

//or
$proxy=new Proxy(new B);
echo isset($proxy->private_prop);// check $obj(B)::$private_prop
echo isset($proxy(A::class)->private_prop);// $obj(A)::$private_prop

```

## Unset  property

```php

use Alpa\Tools\Sucker\SuckerObjectHandlers;
use Alpa\Tools\Sucker\Sucker;
use Alpa\Tools\Sucker\Proxy;


$handler=new SuckerObjectHandlers(new B);

$handler->unset('private_prop'); //unset $obj(B)::$private_prop;
$handler->setScope(A::class)->unset('private_prop'); //unset  $obj(A)::$private_prop;
$handler->setScope(null);
//or
$sucker=new Sucker(new B);
$sucker->unset('private_prop');//check $obj(B)::$private_prop
$sucker(A::class)->unset('private_prop');// check $obj(A)::$private_prop

//or
$proxy=new Proxy(new B);
unset($proxy->private_prop);// check $obj(B)::$private_prop
unset($proxy(A::class)->private_prop);// $obj(A)::$private_prop

```

## Iterate properties

```php

use Alpa\Tools\Sucker\SuckerObjectHandlers;
use Alpa\Tools\Sucker\Sucker;
use Alpa\Tools\Sucker\Proxy;


$handler=new SuckerObjectHandlers(new B);

$handler->each(function($key, &$value){
    // $value variable passed by reference
    echo self::class===B::class; 
    // $this =>object B
    return true; // break
}); //unset $obj(B)::$private_prop;
$handler->setScope(A::class)->each(function ($key,$value){
    echo self::class===A::class; 
    return true; // break
}); 
$handler->setScope(null);

//or
$sucker=new Sucker(new B);
$sucker->each(function($key, &$value){
    // $value variable passed by reference
    echo self::class===B::class; 
    // $this =>object B
    return true; // break
}); 

$sucker(A::class)->each(function ($key,$value){
    echo self::class===A::class; 
    return true; // break
}); 

//or
$proxy=new Proxy(new B);
foreach($proxy as $key => $value){
    // your code
}
foreach($proxy(A::class) as $key => $value){
    // your code
}
```

## Call methods

```php
<?php 

use Alpa\Tools\Sucker\SuckerObjectHandlers;
use Alpa\Tools\Sucker\Sucker;
use Alpa\Tools\Sucker\Proxy;

$handler=new SuckerObjectHandlers(new B);
$arg='hello'; // passing a variable by reference
$var = $handler->call('private_method',$arg);// HELLO 
$var =  $handler->setScope(A::class)->call('private_method',$arg);// hello
// return by reference
$var = & $handler->call('private_methodByReference',$arg);// $var = &$arg variables are linked by reference
$handler->setScope(null);
unset($var,$arg);
//or
$sucker=new Sucker(new B);
$arg='hello';
$var = $sucker->call('private_method','hello');// HELLO
$var = $sucker(A::class)->call('private_method','hello');// hello
//return by reference
$var = & $sucker(A::class)->call('private_method','hello');// hello
// arguments by reference
$var = & $sucker(A::class)->apply('private_methodByReference',[&$arg]); // $var = &$arg variables are linked by reference
unset($var,$arg);
//or 
$proxy=new Proxy(new B);
$var = $proxy->private_method('hello');// HELLO
$var = $proxy(A::class)->private_method('hello');// hello
//return by reference
$var = & $proxy(A::class)->private_method('hello');
```

## Sandbox

```php
<?php 

use Alpa\Tools\Sucker\SuckerObjectHandlers;
use Alpa\Tools\Sucker\Sucker;
use Alpa\Tools\Sucker\Proxy;

$handler=new SuckerObjectHandlers(new B);
$arg='hello'; // passing a variable by reference
$var = $handler->sandbox(function($arg){
    echo self::class===B::class;
    // $this - object B
    return $arg;
},[$arg]);// hello 

$var = $handler->setScope(A::class)->sandbox(function($arg){
    echo self::class===A::class;
    // $this - object A
    return $arg;
},[$arg]);// hello 
 
 //references
$var = & $handler->sandbox(function & (&$arg){
    return $arg;
},[&$arg]);// $var = &$arg variables are linked by reference
$handler->setScope(null);
unset($var,$arg);
//or
$sucker=new Sucker(new B);
$arg='hello';
$var = $sucker->sandbox(function($arg){
    echo self::class===B::class;
    // $this - object B
    return $arg;
},[$arg]);
$var = $sucker(A::class)->setScope(A::class)->sandbox(function($arg){
    echo self::class===A::class;
    // $this - object A
    return $arg;
},[$arg]);
// references
$var = & $sucker(A::class)->sandbox(function & (&$arg){
    return $arg;
},[&$arg]); // $var = &$arg variables are linked by reference
unset($var,$arg);

//or 
$proxy=new Proxy(new B);
$arg='hello';
$var = $proxy(function($arg){
    echo self::class===B::class;
     // $this - object B
    return $arg;
},[$arg]);// hello

$var = $proxy(A::class)(function($arg){
    echo self::class===A::class;
     // $this - object A
    return $arg;
},[$arg]);// hello

//references
$var = & $proxy(function & (&$arg){
    return $arg;
}); // $var = &$arg variables are linked by reference
```