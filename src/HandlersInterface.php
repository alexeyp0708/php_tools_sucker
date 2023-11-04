<?php

namespace Alpa\Tools\Sucker;

use Alpa\Tools\ProxyObject\Handlers\ActionsInterface;

interface HandlersInterface extends ActionsInterface 
{
    public function __construct($target);

    public function setScope(?string $scope): void;

    public function getScope(): string;

    public function & sandbox(\Closure $call, ...$args);
}