<?php

namespace Alpa\Tools\Sucker;

/** @deprecated  */
class SuckerData
{
    public ?string $member;
    public string $action;
    public array $arguments = [];

    public function __construct(?string $member = null, $action = 'get', &...$args)
    {
        $this->member = $member;
        $this->action = $action;
        $this->arguments = $args;//&
    }
}