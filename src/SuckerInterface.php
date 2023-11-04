<?php


namespace Alpa\Tools\Sucker;


interface SuckerInterface
{
    public function __invoke(?string $scope): self;

    public function getScope(): string;

    public function & get(string $member);

    public function set(string $member, $value): void;

    public function setRef(string $member, &$value): void;

    public function isset(string $member): bool;

    public function unset(string $member): void;

    public function each(\Closure $call, ?string $class = null): void;

    public function & call(string $member, ...$args);

    public function & apply(string $member, array $args);

    public function & sandbox(\Closure $action, array $args = []);
}