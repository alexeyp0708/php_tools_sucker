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
    private ?string $scope;
    private string $default_scope;

    /**
     * Sucker constructor.
     * @param object|string|null $target If a string is passed, then the class name
     */
    public function __construct($target, ?SuckerHandlersInterface $handlers = null)
    {

        $this->target = $target;
        if ($handlers === null) {
            if (!is_string($target)) {
                $handlers = new SuckerObjectHandlers();
            } else {
                $handlers = new SuckerClassHandlers();
            }
        }
        $this->handlers = $handlers;
        $handlers->setSubject($target);
        if (is_string($target)) {
            $this->default_scope = $target;
        } else {
            $this->default_scope = get_class($target);
        }
    }

    public function __invoke(?string $scope): self
    {
        $this->scope = $scope;
        return $this;
    }

    private function resetScope(): self
    {
        $this->scope = null;
        return $this;
    }

    private function getScope(): string
    {
        return $this->scope ?? $this->default_scope;
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
        $answer =& $this->handlers->setScope($this->getScope())->get($member);
        $this->resetScope();
        return $answer;
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
        $this->handlers->setScope($this->getScope())->set($member, $value);
        $this->resetScope();
    }

    /**
     *
     * @param string $member
     * @param $value
     */
    public function setRef(string $member, &$value): void
    {
        $this->handlers->setScope($this->getScope())->set($member, $value);
        $this->resetScope();
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
        $answer = $this->handlers->setScope($this->getScope())->isset($member);
        $this->resetScope();
        return $answer;
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
        try {
            $this->handlers->setScope($this->getScope())->unset($member);
        } finally {
            $this->resetScope();
        }

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
        $this->handlers->setScope($class ?? $this->getScope())->each($call);
        $this->resetScope();
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
        $answer = &$this->handlers->setScope($this->getScope())->call($member, ...$args);
        $this->resetScope();
        return $answer;
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
    public function & sandbox(\Closure $action, array $args = [])
    {
        $answer = &$this->handlers->setScope($this->getScope())->sandbox($action, $args);
        $this->resetScope();
        return $answer;
    }
}