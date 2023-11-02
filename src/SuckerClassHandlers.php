<?php


namespace Alpa\Tools\Sucker;


class SuckerClassHandlers implements SuckerHandlersInterface
{
    private string $subject;
    private ?string $scope = null;
    private string $selfClass;

    final public function setSubject($subject): self
    {
        $this->subject = $subject;
        $this->selfClass = $subject;
        return $this;
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
        $call = (SuckerActions::getAction('get', true))->bindTo(null, $this->scope ?? $this->selfClass);
        return $call($member);
    }

    public function set(string $member, &$value): void
    {
        $call = (SuckerActions::getAction('set', true))->bindTo(null, $this->scope ?? $this->selfClass);
        $call($member, $value);
    }

    public function & call($member, &...$args)
    {
        $call = (SuckerActions::getAction('call', true))->bindTo(null, $this->scope ?? $this->selfClass);
        return $call($member, ...$args);
    }

    public function each(callable $each): void
    {
        $each = $each->bindTo(null, $this->scope ?? $this->selfClass);
        $call = (SuckerActions::getAction('each', true))->bindTo(null, $this->scope ?? $this->selfClass);
        $call($each);
    }

    public function isset($member): bool
    {
        $call = (SuckerActions::getAction('isset', true))->bindTo(null, $this->scope ?? $this->selfClass);
        return $call($member);
    }

    public function unset($member): void
    {
        $call = (SuckerActions::getAction('unset', true))->bindTo(null, $this->scope ?? $this->selfClass);
        $call($member);
    }

    public function & sandbox(\Closure $call, $args = [])
    {
        $call = $call->bindTo(null, $this->scope ?? $this->selfClass);

        if ((new \ReflectionFunction($call))->returnsReference()) {
            $answer = &$call(...$args);
        } else {
            $answer = $call(...$args);
        }
        return $answer;
    }
}