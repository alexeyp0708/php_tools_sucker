<?php

namespace Alpa\Tools\Sucker;

interface HandlersInterface
{
    public function __construct($target);

    public function setScope(?string $scope): void;

    public function getScope(): string;
    
    public function & sandbox(\Closure $call,...$args);
}