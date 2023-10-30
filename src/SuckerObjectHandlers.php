<?php


namespace Alpa\Tools\Sucker;


class SuckerObjectHandlers  implements SuckerHandlersInterface
{
    private object $subject;
    private ?string $scope = null;
    private string $selfClass;

    final public function setSubject($subject): self
    {
        $this->subject = $subject;
        $this->selfClass = get_class($subject);
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
        $call = (SuckerActions::getAction('get'))->bindTo($this->subject, $this->scope ?? $this->selfClass);
        return $call($member);
    }

    public function set(string $member, &$value): void
    {
        $call = (SuckerActions::getAction('set'))->bindTo($this->subject, $this->scope ?? $this->selfClass);
        $call($member,$value);
    }

    public function & call($member, &...$args)
    {
        $call = (SuckerActions::getAction('call'))->bindTo($this->subject, $this->scope ?? $this->selfClass);
        return $call($member, ...$args);
    }

    public function each(callable $each): void
    {
        $each=$each->bindTo($this->subject, $this->scope ?? $this->selfClass);
        $call = (SuckerActions::getAction('each'))->bindTo($this->subject, $this->scope ?? $this->selfClass);
        $call($each);
    }

    public function isset($member): bool
    {
        $call = (SuckerActions::getAction('isset'))->bindTo($this->subject, $this->scope ?? $this->selfClass);
        return $call($member);
    }

    public function unset($member): void
    {
        $call = (SuckerActions::getAction('unset'))->bindTo($this->subject, $this->scope ?? $this->selfClass);
        $call($member);
    }

    public function & sandbox(\Closure $call, $args)
    {
        $call = $call->bindTo($this->subject, $this->scope ?? $this->selfClass);
        if ((new \ReflectionFunction($call))->returnsReference()) {
            $answer = &$call(...$args);
        } else {
            $answer = $call(...$args);
        }
        return $answer;
    }
}