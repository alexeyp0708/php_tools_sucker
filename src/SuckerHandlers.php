<?php


namespace Alpa\Tools\Sucker;


class SuckerHandlers implements SuckerHandlersInterface
{
    private object $subject;
    private ?string $scope = null;
    private string $selfClass;

    final public function setSubject($subject): void
    {
        $this->subject = $subject;
        $this->selfClass = get_class($subject);
    }

    final public function getSubject($subject): object
    {
        return $this->subject;
    }

    final public function setScope(?string $class): self
    {
        $this->scope = $class;
        return $this;
    }

    final public function & get(string $member)
    {
        $call = (function & ($member) {
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
        })->bindTo($this->subject, $this->scope ?? $this->selfClass);
        return $call($member);
    }

    public function set(string $member, &$value): void
    {
        $call = (function  ($member,&$value) {
            $this->$member = &$value;
        })->bindTo($this->subject, $this->scope ?? $this->selfClass);
        $call($member,$value);
    }

    public function & call($member, &...$args)
    {
        $call = (function & ($member, &...$args) {
            if ((new \ReflectionMethod($this, $member))->returnsReference()) {
                $answer = &$this->$member(...$args);
            } else {
                $answer = $this->$member(...$args);
            }
            return $answer;
        })->bindTo($this->subject, $this->scope ?? $this->selfClass);
        return $call($member, ...$args);
    }

    public function each(callable $each): void
    {
        $each=$each->bindTo($this->subject, $this->scope ?? $this->selfClass);
        $call = (function ($each)  {
            foreach ($this as $key => & $value) {
                if (true === $each($key, $value)) {
                    break;
                };
            }
            unset($value);
        })->bindTo($this->subject, $this->scope ?? $this->selfClass);
        $call($each);
    }

    public function isset($member): bool
    {
        $call = (function ($member) {
            return isset($this->$member);
        })->bindTo($this->subject, $this->scope ?? $this->selfClass);
        return $call($member);
    }

    public function unset($member): void
    {
        $call = (function ($member) {
            unset($this->$member);
        })->bindTo($this->subject, $this->scope ?? $this->selfClass);
        $call($member);
    }

    public function & sandbox(callable $call, $args)
    {
        if (is_array($call)) {
            $ref = new \ReflectionMethod($call[0], $call[1]);
            if (is_string($call[0])) {
                $call = $ref->getClosure();
            } else {
                $call = $ref->getClosure($call[0]);
            }
        } else if (is_string($call)) {
            $ref = new \ReflectionFunction($call);
            $call = $ref->getClosure();
        }
        $call = $call->bindTo($this->subject, $this->scope ?? $this->selfClass);
        if ((new \ReflectionFunction($call))->returnsReference()) {
            $answer = &$call(...$args);
        } else {
            $answer = $call(...$args);
        }
        return $answer;
    }
}