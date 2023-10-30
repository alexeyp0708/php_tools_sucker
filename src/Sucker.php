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
    private $target;
    private SuckerHandlersInterface $handlers;
    private string $slaveClass;

    /**
     * Sucker constructor.
     * @param object|string|null $target If a string is passed, then the class name
     */
    public function __construct($target, ?SuckerHandlersInterface $handlers = null)
    {

        $this->target = $target;
        if ($handlers === null) {
            if (!is_string($target)) {
                $handlers = new SuckerHandlers();
            } else {
                $handlers = new SuckerClassHandlers();
            }
        }
        $this->handlers = $handlers;
        $handlers->setSubject($target);
        if (is_string($target)) {
            $this->slaveClass = $target;
        } else {
            $this->slaveClass = get_class($target);
        }
    }

    public function __invoke(string $slaveClass): self
    {
        $this->slaveClass = $slaveClass;
        return $this;
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
        $answer =& $call(...$args);
        restore_error_handler();
        return $answer;
    }


    public function & run(string $action, ?string $member = null, $args = [])
    {
        $slaveClass = !empty($class) ? $class : $this->slaveClass;
        switch ($action) {
            case 'get':
            case 'call':
            case 'sandbox':
                $answer = &$this->$action($member, ...$args);
                break;
            case 'isset':
                $answer = $this->$action($member);
                break;
            case 'each':
                $this->$action(...$args);
                break;
            case 'set':
                $this->$action($member, ...$args);
                $answer = null;
                break;
            case 'unset':
                $this->$action($member);
                $answer = null;
        }
        return $answer;
    }

    /**
     * @deprecated
     */
    private static function refNoticeErrorHandler(bool $prev_restore = false)
    {
        $prev_handler_error = null;
        $prev_handler_error = set_error_handler(function (...$args) use (&$prev_handler_error, $prev_restore) {

            if (in_array($args[1], [
                'Only variables should be assigned by reference',
                'Only variable references should be returned by reference'])) {
                return true;
            }
            if (!is_null($prev_handler_error)) {
                $answer = $prev_handler_error(...$args);
                if (is_bool($answer)) {
                    return $answer;
                }
            }
            return false;
        }, E_NOTICE | E_WARNING);
    }

    /**
     * @deprecated
     * initializes actions (Closures).
     */
    protected static function initActions()
    {
        if (!empty(static::$actions)) {
            return;
        }
        static::$actions = (object)[
            'get' => function & (string $member) {
                $error_msg = '';
                $error_code = 0;
                $handler = set_error_handler(function (...$args) use (&$error_msg, &$error_code) {
                    if (substr($args[1], 0, 19) === 'Undefined property:') {
                        $bt = debug_backtrace()[3];
                        $error_msg = $args[1] . ' in ' . $bt['file'] . ' on line ' . $bt['line'] . "\n";
                        $error_code = $args[0];
                        return true;
                    }
                    return false;
                }, E_NOTICE | E_WARNING);
                $res = $this->$member;
                restore_error_handler();
                //For some reason, the restored handler does not run when trigger_error
                if ($error_code > 0) {
                    if ($handler !== null) {
                        set_error_handler($handler);
                    } // forcefully restore an error handler
                    trigger_error($error_msg, $error_code === E_NOTICE ? E_USER_NOTICE : E_USER_WARNING);
                    if ($handler !== null) {
                        restore_error_handler();
                    }
                    return $res;
                }
                return $this->$member;
            },
            'static_get' => function & (string $member) {
                return self::$$member;
            },
            'set' => function (string $member, $value): void {
                $this->$member = $value;
                //  we secure ourselves and destroy additional reference
                //unset($value);
            },
            'setRef' => function (string $member, &$value): void {
                $this->$member = &$value;
                //  we secure ourselves and destroy additional reference
                //unset($value);
            },
            'static_set' => function (string $member, $value): void {
                self::$$member = $value;
            },
            'static_setRef' => function (string $member, &$value): void {
                self::$$member = &$value;
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
                foreach ($this as $key => & $value) {
                    if (true === $each($key, $value)) {
                        break;
                    };
                }
                unset($value);
            },
            'static_each' => function (?string $member, \Closure $each) {
                $each = $each->bindTo(null, self::class);
                $vars = (new \ReflectionClass(self::class))->getStaticProperties();
                foreach ($vars as $key => & $value) {
                    $value = &self::$$key;
                    if (true === $each($key, $value)) {
                        break;
                    };
                }
                unset($value);
            },
            'call' => function & ($member, &...$args) {
                if ((new \ReflectionMethod($this, $member))->returnsReference()) {
                    $answer = &$this->$member(...$args);
                } else {
                    $answer = $this->$member(...$args);
                }
                return $answer;
            },
            'static_call' => function & ($member, &...$args) {
                if ((new \ReflectionMethod(self::class, $member))->returnsReference()) {
                    $answer = &self::{$member}(...$args);
                } else {
                    $answer = self::{$member}(...$args);
                }
                return $answer;
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
     * @deprecated
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
        $name = null;
        $class = null;
        if ($member !== null) {
            $member = self::parseFullName($member);
            $name = $member->name;
            $class = $member->class;
        }
        $slaveClass = !empty($class) ? $class : $this->slaveClass;
        return $this->handlers->setScope($slaveClass)->get($name);
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
        $name = null;
        $class = null;
        if ($member !== null) {
            $member = self::parseFullName($member);
            $name = $member->name;
            $class = $member->class;
        }
        $slaveClass = !empty($class) ? $class : $this->slaveClass;
        $this->handlers->setScope($slaveClass)->set($name, $value);
    }

    /**
     *
     * @param string $member
     * @param $value
     */
    public function setRef(string $member, &$value): void
    {
        $name = null;
        $class = null;
        if ($member !== null) {
            $member = self::parseFullName($member);
            $name = $member->name;
            $class = $member->class;
        }
        $slaveClass = !empty($class) ? $class : $this->slaveClass;
        $this->handlers->setScope($slaveClass)->set($name, $value);
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
        $name = null;
        $class = null;
        if ($member !== null) {
            $member = self::parseFullName($member);
            $name = $member->name;
            $class = $member->class;
        }
        $slaveClass = !empty($class) ? $class : $this->slaveClass;
        return $this->handlers->setScope($slaveClass)->isset($name);
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
        $name = null;
        $class = null;
        if ($member !== null) {
            $member = self::parseFullName($member);
            $name = $member->name;
            $class = $member->class;
        }
        $slaveClass = !empty($class) ? $class : $this->slaveClass;
        $this->handlers->setScope($slaveClass)->unset($name);
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
        $slaveClass = !empty($class) ? $class : $this->slaveClass;
        $this->handlers->setScope($slaveClass)->each($call);
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
        return $this->apply($member, $args);
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
        $name = null;
        $class = null;
        if ($member !== null) {
            $member = self::parseFullName($member);
            $name = $member->name;
            $class = $member->class;
        }
        $slaveClass = !empty($class) ? $class : $this->slaveClass;
        return $this->handlers->setScope($slaveClass)->call($name, ...$args);
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
        /*if ($class !== null && substr($class, -2) != '::') {
            $class .= '::';
        }*/
        $class = is_string($class) ? trim($class, ':') : null;
        return $this->handlers->setScope($class ?? $this->slaveClass)->sandbox($action, $args);
    }
}