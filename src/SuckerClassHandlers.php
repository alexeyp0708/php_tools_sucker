<?php


namespace Alpa\Tools\Sucker;


class SuckerClassHandlers implements SuckerHandlersInterface
{
    private string $subject;
    private ?string $scope = null;
    private string $selfClass;

    final public function setSubject($subject): void
    {
        $this->subject = $subject;
        $this->selfClass = $subject;
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
            return self::${$member};
        })->bindTo(null, $this->scope ?? $this->selfClass);
        return $call($member);
    }

    public function set(string $member, &$value): void
    {
        $call = (function  ($member,&$value) {
            self::$$member = &$value;
        })->bindTo(null, $this->scope ?? $this->selfClass);
        $call($member,$value);
    }

    public function & call($member, &...$args)
    {
        $call = (function & ($member, &...$args) {
            if ((new \ReflectionMethod(self::class,$member))->returnsReference()) {
                $answer = &self::{$member}(...$args);
            } else {
                $answer = self::{$member}(...$args);
            }
            return $answer;
        })->bindTo(null, $this->scope ?? $this->selfClass);
        return $call($member, ...$args);
    }

    public function each(callable $each): void
    {
        $each=$each->bindTo(null, $this->scope ?? $this->selfClass);
        $call = (function ($each)  {
            $vars = (new \ReflectionClass(self::class))->getStaticProperties();
            foreach ($vars as $key => & $value) {
                $value = & self::$$key;
                if (true === $each($key, $value)) {
                    break;
                };
            }
            unset($value);
        })->bindTo(null, $this->scope ?? $this->selfClass);
        $call($each);
    }

    public function isset($member): bool
    {
        $call = (function ($member) {
            return isset(self::${$member});
        })->bindTo(null, $this->scope ?? $this->selfClass);
        return $call($member);
    }

    public function unset($member): void
    {
        $call = (function ($member) {
            unset(self::${$member});
        })->bindTo(null, $this->scope ?? $this->selfClass);
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
        $call = $call->bindTo(null, $this->scope ?? $this->selfClass);
        
        if ((new \ReflectionFunction($call))->returnsReference()) {
            $answer = &$call(...$args);
        } else {
            $answer = $call(...$args);
        }
        return $answer;
    }
}