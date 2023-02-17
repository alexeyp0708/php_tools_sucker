<?php

namespace Alpa\Tools\Tests\Sucker\Fixtures;

class Sucker extends \Alpa\Tools\Sucker\Sucker
{
    public static object $actions;
    public \Closure $runner;
    public $target;

    public static function initActions()
    {
        parent::initActions();
    }
}