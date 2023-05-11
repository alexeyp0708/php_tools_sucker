<?php

namespace Alpa\Tools\Sucker;
/**
 * Class Sucker
 * Its task is to work with protected / private properties and methods implemented in the object's classes
 * (current class and parent classes). Performs actions such as get | set | unset | isset | each | call | sandbox
 */
class Sucker
{
    protected static object $actions;
    protected \Closure $runner;
    protected $target;
    /*    protected array $options = [
            'reference' => true
        ];*/

    /**
     * Sucker constructor.
     * @param object|string|null $target If a string is passed, then the class name
     */
    public function __construct($target = null)//, array $options = []
    {
        if (!empty($target)) {
            $this->init($target);
        }
        /*        foreach ($options as $key => $value) {
                    if ($this->options[$key]) {
                        $this->options[$key] = $value;
                    }
                }*/
    }
    /*
        public function setOption($key, $value)
        {
            if ($this->options[$key]) {
                $this->options[$key] = $value;
            }
        }*/

    /**
     * Initializes the target with which it will work. ($this or self in Closure)
     * @param object|string $target If a string is passed, then the class name
     */
    public function init($target)
    {
        $this->target = $target;
    }

    /**
     * Static sandbox launch option
     * @param \Closure $call function(...$args):mixed
     * $call  will bind to $target
     * @param object|string $target Will $this/self in $call function
     * @param string|null $slaveClass if is not null then will "self" in $call function
     * @param array $args arguments to $call function
     * @return mixed result $call function
     */
    public static function & sbox(\Closure $call, $target, ?string $slaveClass = null, array $args = [])
    {
        $slaveClass = !empty($slaveClass) ? $slaveClass : (is_string($target) ? $target : get_class($target));
        $target = !is_string($target) ? $target : null;
        $call = $call->bindTo($target, $slaveClass);
        self::refNoticeErrorHandler();
        $answer=&$call(...$args);
        restore_error_handler();
        return $answer;
    }

    /**
     * Run action
     * @param string|\Closure $action
     * action for property - get|set|isset|unset|each
     * action for method - call
     * custom action -Closure object
     * @param string|null $member
     * @param array $args
     * @return mixed
     */
    protected function & run($action, ?string $member = null, $args = [])
    {
        if (empty(static::$actions)) {
            static::initActions();
        }
        $name = null;
        $class = null;
        if ($member !== null) {
            $member = self::parseFullName($member);
            $name = $member->name;
            $class = $member->class;
        }
        $isSandbox = false;
        if ($action instanceof \Closure) {
            $isSandbox = true;
        } else {
            $action = (is_string($this->target) ? 'static_' : '') . $action;
            $action = static::$actions->$action;
        }
        $target = !is_string($this->target) ? $this->target : null;
        $slaveClass = !empty($class) ? $class : (is_string($this->target) ? $this->target : get_class($this->target));
        $action = $action->bindTo($target, $slaveClass);
        self::refNoticeErrorHandler();
        //working line
        if($isSandbox){
            $answer=& $action(...$args);
        } else {
            $answer=& $action($name, ...$args);
        }
        restore_error_handler();
        return $answer;
    }

    private static function refNoticeErrorHandler(bool $prev_restore = false)
    {
        $prev_handler_error = null;
        $prev_handler_error = set_error_handler(function (...$args) use (&$prev_handler_error, $prev_restore) {
            if ($args[0] == 8) {
                if ($prev_restore) {
                    restore_error_handler();
                }
                return true;
            }
            return $prev_handler_error($args);
        }, E_NOTICE);
    }

    /**
     * initializes actions (Closures).
     */
    protected static function initActions()
    {
        if (!empty(static::$actions)) {
            return;
        }
        static::$actions = (object)[
            'get' => function & (string $member) {
                return $this->$member;
            },
            'static_get' => function & (string $member) {
                return self::$$member;
            },
            'set' => function (string $member, &$value): void {
                $this->$member = $value;
            },
            'static_set' => function (string $member, &$value): void {
                self::$$member = $value;
            },
            'unset' => function (string $member): void {
                unset($this->$member);
            },
            'static_unset' => function (string $member): void {
                // generate error
                unset(self::$$member);
            },
            'isset' => function (string $member): bool {
                return isset($this->$member);
            },
            'static_isset' => function (string $member): bool {
                return isset(self::$$member);
            },
            'each' => function (?string $member, \Closure $each) {
                $each = $each->bindTo($this, self::class); 
                foreach ($this as $key => $value) {
                    if (true === $each($key, $value)) {
                        break;
                    };
                }
            },
            'static_each' => function (?string $member, callable $each) {
                $each = $each->bindTo(null, self::class);
                $vars = (new \ReflectionClass(self::class))->getStaticProperties();
                foreach ($vars as $key => $value) {
                    if (true === $each($key, $value)) {
                        break;
                    };
                }
            },
            'call' => function & ($member, &...$args) {
                // Notice: Only variables should be assigned by reference - drown out
                return $this->$member(...$args);
            },
            'static_call' => function & ($member, &...$args) {
                // Notice: Only variables should be assigned by reference - drown out
                return self::{$member}(...$args);
            },
            /*'sandbox'=>function($member=null,\Closure $call,...$args){
                $call = $call->bindTo($this, self::class);
                return $call(...$args);
            },
            'static_sandbox'=>function($member=null,\Closure $call,...$args){
                $call = $call->bindTo(null, self::class);
                return $call(...$args);
            }*/
        ];
    }

    /**
     * Parses the full name of a property / class
     * @param $fullName
     * Class::method =>class + method
     * method => current method
     * ::method=> current method
     * Class:: => class
     * @return object {class:?string,name:?string,fullName:string}
     */
    protected static function parseFullName($fullName): object
    {
        $parts = $fullName;
        $result = (object)[];
        if (is_string($parts)) {
            $parts = explode('::', $parts);
        }
        if (count($parts) === 1) {
            $result->class = null;
            $result->name = $parts[0];
            $result->fullName = '::' . $parts[0];
        } else if ($parts[0] === '') {
            $result->class = null;
            $result->name = $parts[1];
            $result->fullName = '::' . $parts[1];
        } else {
            $result->class = $parts[0];
            $result->name = $parts[1];
            $result->fullName = $parts[0] . '::' . $parts[1];
        }
        return $result;
    }

    /**
     * action get - Requests a property of an object / class
     * If self :: target is a class, it will request static properties
     * @param string $member
     * property, ::property - current properties
     * ParentClass::property - properties of the parent class
     * @return mixed
     */
    public function & get(string $member)
    {
        return $this->run('get', $member);
    }

    /**
     * action set - sets a value for a property
     * If self :: target is a class, it will set static properties
     * If the static property is absent in the class, then an error will be thrown
     * @param string $member
     * property, ::property - current properties
     * ParentClass::property - properties of the parent class
     * @param $value
     */
    public function set(string $member, $value): void
    {
        $this->run('set', $member, [$value]);
    }

    /**
     *
     * @param string $member
     * @param $value
     */
    public function setRef(string $member, &$value): void
    {
        $this->run('set', $member, [&$value]);
    }

    /**
     * checks if there is a property
     * If self :: target is a class, it will check static properties
     * @param string $member
     * property, ::property - current properties
     * ParentClass::property - properties of the parent class
     * @return bool
     */
    public function isset(string $member): bool
    {
        return $this->run('isset', $member);
    }

    /**
     * delete property
     * If self :: target is a class, then it will try to remove the static property and cause an error
     * @param string $member
     * property, ::property - current properties
     * ParentClass::property - properties of the parent class
     */
    public function unset(string $member): void
    {
        $this->run('unset', $member);
    }

    /**
     * iterates over the properties of the class
     * If self :: target is a class, it will iterates static properties
     * @param \Closure $call function (string $key,mixed $value):bool|void
     * $call  will bind to self::target
     * - if $call return true, then break;
     * @param string|null $class NameClass or NameClass::
     * The parent class with whose properties you want to work (reflect)
     */
    public function each(\Closure $call, ?string $class = null): void
    {
        if ($class !== null && substr($class, -2) != '::') {
            $class .= '::';
        }
        $this->run('each', $class, [$call]);
    }

    /**
     * @param string $member
     * method, ::method - current method
     * ParentClass::method - method of the parent class
     * @param mixed ...$args arguments to method
     * @return mixed result method
     */
    public function & call(string $member, ...$args)
    {
        return $this->run('call', $member, $args);
    }

    /**
     * Will call the object / class method
     * To pass arguments by value and reference - `$this->apply('method', ['hello',$var,&$ref_var]);`
     * @param string $member
     * @param array $args
     * @return mixed
     */
    public function & apply(string $member, array $args)
    {
        return $this->run('call', $member, $args);
    }

    /**
     * Will perform custom actions
     * @param \Closure $action function(...$args):mixed
     * $call  will bind to self::target
     * @param string|null $class NameClass or NameClass::
     * The parent class with whose properties you want to work (reflect)
     * @param array $args arguments to $call function
     * @return mixed result $call function
     */
    public function & sandbox(\Closure $action, ?string $class = null, array $args = [])
    {
        if ($class !== null && substr($class, -2) != '::') {
            $class .= '::';
        }
        return $this->run($action, $class, $args);
    }
}