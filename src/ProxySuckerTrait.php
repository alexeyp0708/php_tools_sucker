<?php

namespace Alpa\Tools\Sucker;

trait ProxySuckerTrait
{
    public function __invoce($scope=null)
    { 
        return new Proxy($this,SuckerHandlers::class,$scope);
    }
}