<?php
/** @deprecated  */
include __DIR__ . '/../vendor/autoload.php';

class A
{
    private $prop = 'bay';
    private static $static_prop = 'bay';

    private function method()
    {
        return 'bay';
    }

}

;

class B extends A
{
    private $prop = 'hello';
    private static $static_prop = 'hello';

    private function method()
    {
        return 'hello';
    }

    private function & method_reference(&$arg)
    {
        return $arg;
    }

    private $var = 'hello';

    public function getVar()
    {
        return $this->var;
    }

    private $qwer = 'qwer';

    private function & method_ref_prop()
    {
        return $this->qwer;
    }
}

;
$target = new B();
$target2 = B::class;
$sucker = new \Alpa\Tools\Sucker\Sucker($target);
$sucker_static = new \Alpa\Tools\Sucker\Sucker($target2);

// returns value properties
echo $sucker->get('prop');//  'hello'
echo "\n";
echo $sucker->get('A::prop');// 'bay'
echo "\n";
// returns value static properties
echo $sucker_static->get('static_prop');// return 'hello'
echo "\n";
echo $sucker_static->get('A::static_prop');// return 'bay'
echo "\n";

// returns by reference
$var = &$sucker->get('prop');// hello
$var = 'HELLO';
echo $sucker->get('prop');// HELLO
echo "\n";
$var = 'hello';// value restore
unset($var);

$var = &$sucker->get('A::prop');// bay
$var = 'BAY';
echo $sucker->get('A::prop');// BAY
echo "\n";
$var = 'bay';// value restore
unset($var);


// call methods
echo $sucker->call('method');// 'hello' 
echo "\n";
echo $sucker->call('A::method');// 'bay' 
echo "\n";
//  apply methods (test references)
$test = 'test';
$result =& $sucker->apply('method_reference', [&$test]);
$test = 'TEST';
echo $result . '==' . $test; // TEST == TEST
echo "\n";
// set value properties
$sucker->set('prop', 'other hello');// void;

// set value properties by reference

$value = 'other hello';
$sucker->setRef('prop', $value);// void;
$value = 'OTHER HELLO';
echo $sucker->get('prop');//'OTHER HELLO'
echo "\n";
unset($value);
$sucker->set('prop', 'hello');

// isset member
echo $sucker->isset('prop'); // true;
echo "\n";


//for each
$sucker->each(function ($key, $value) {
    echo $key . ' => ' . $value; // prop => hello
    echo "\n";
}); // void;

$sucker->each(function ($key, $value) {
    echo $key . ' => ' . $value; // prop => bay
    echo "\n";
}, 'A'); // void;

$sucker->each(function ($key, &$value) {
    echo $key . ' => ' . $value; // prop => hello
    echo "\n";
    if ($key === 'prop') {
        $value = 'HELLO';
    }

}); // void;
echo $sucker->get('prop');
echo "\n";
$sucker->set('prop', 'hello');
// unset member
$sucker->unset('prop'); // void;

//echo $sucker->get('prop');
//echo $sucker->get('var');

unset($var);
$proxy = new \Alpa\Tools\Sucker\Proxy($target);
$var =& $proxy->method_ref_prop();
echo $var;
echo "\n";
$var = 'QWER';
echo $proxy->method_ref_prop();
echo "\n";
unset($var);
$var = &$proxy->qwer;

$var = 'asdf';
echo $proxy->qwer;
echo "\n";
echo "\n";