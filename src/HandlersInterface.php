<?php

namespace Alpa\Tools\Sucker;

interface HandlersInterface
{
    public function __construct($target, string $scope);

    public function setScope(string $scope): void;

    public function getScope(): string;

    public function initDefaultScope(): void;

    public function restoreDefaultScope(): void;
}