<?php

namespace Alpa\Tools\Sucker;

use Alpa\Tools\ProxyObject\Handlers\InstanceActions;
use Alpa\Tools\ProxyObject\Proxy;

class SuckerProxyHandlers  extends InstanceActions implements HandlersInterface 
{
    private Sucker $sucker;
    public function __construct($target)
    {
        $this->sucker=new Sucker($target);
    }
    
    public function setScope(?string $scope):void
    {
        ($this->sucker)($scope);
    }

    public function getScope():string
    {
        return $this->sucker->getScope();
    }
    
    public function & sandbox(\Closure $call, ...$args)
    {
        return $this->sucker->sandbox($call,...$args);
    }

    public  function & get($target, string $prop, $value_or_args, Proxy $proxy)
    {
       return $this->sucker->get($prop);
    }

    public  function set($target, string $prop, $value_or_args, Proxy $proxy): void
    {
        $this->sucker->set($prop,$value_or_args);
    }

    public function isset($target, string $prop, $value_or_args, Proxy $proxy):bool
    {
        return $this->sucker->isset($prop);
    }
    public function unset($target, string $prop, $value_or_args, Proxy $proxy):void
    {
        $this->sucker->unset($prop);
    }
    public function & call($target, string $prop, $value_or_args, Proxy $proxy)
    {
        return $this->sucker->call($prop,...$value_or_args);
    }
    public function iterator($target,$prop,$value_or_args,Proxy $proxy):\Iterator
    {
        if(is_object($target) && ($target instanceof \IteratorAggregate)){
            return $target->getIterator();
        } 
        if(is_string($target)){
            return new SuckerClassIterator($this->sucker,$this->getScope());
        }
        return new SuckerIterator($this->sucker,$this->getScope());
    }
}