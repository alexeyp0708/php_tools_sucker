<?php

namespace Alpa\Tools\Sucker;

use Alpa\Tools\ProxyObject\Handlers\InstanceActions;
use Alpa\Tools\ProxyObject\Proxy;

class SuckerProxyHandlers  extends InstanceActions implements HandlersInterface 
{
    private string $scope;
    private string $default_scope;
    private Sucker $sucker;
    public function __construct($target,string $scope=null)
    {
        if($scope===null){
            if (is_string($target)) {
                $scope=$target;
            } else if(is_object($target)) {
                $scope=get_class($target);
            }
        }
        $this->sucker=new Sucker($target);
        $this->setScope($scope);
        $this->initDefaultScope();
    }
    
    public function setScope(string $scope):void
    {
        $scope!==null && class_exists($scope);
        $this->scope=$scope;
    }

    public function getScope():string
    {
        return $this->scope;
    }
    public function initDefaultScope():void
    {
        $this->default_scope=$this->scope;
    }
    public function restoreDefaultScope():void
    {
        $this->scope=$this->default_scope;
    }
/*    public function run (string $action, $target, ?string $prop, $value_or_args, Proxy $proxy)
    {
        if (!in_array($action, ['get', 'set', 'isset', 'unset', 'call', 'toString', 'iterator'])) {
            throw new \Exception('Action must be one of the values "get|set|isset|unset|call|toString|iterator"');
        }
        $scope=$this->getScope();
        if($scope!==null){
            $prop=$scope.'::'.$prop;
        }
        if(in_array($action,['get','set','call','isset','unset'])){
            if(!is_array($value_or_args)){
                if($value_or_args===null && $action!=='set'){
                    $value_or_args=[];
                } else {
                    $value_or_args=[$value_or_args];
                }
            }
            return $this->sucker->run($action,$prop,...$value_or_args);
        }
        return $this->$action($target,$prop,$value_or_args,$proxy);
    }*/

    public  function & get($target, string $prop, $value_or_args, Proxy $proxy)
    {
        $scope=$this->getScope();
        if($scope!==null){
            $prop=$scope.'::'.$prop;
        }
        return $this->sucker->get($prop);
    }

    public  function set($target, string $prop, $value_or_args, Proxy $proxy): void
    {
        $scope=$this->getScope();
        if($scope!==null){
            $prop=$scope.'::'.$prop;
        }
        $this->sucker->set($prop,$value_or_args);
    }

    public function isset($target, string $prop, $value_or_args, Proxy $proxy):bool
    {
        $scope=$this->getScope();
        if($scope!==null){
            $prop=$scope.'::'.$prop;
        }
        return $this->sucker->isset($prop);
    }
    public function unset($target, string $prop, $value_or_args, Proxy $proxy):void
    {
        $scope=$this->getScope();
        if($scope!==null){
            $prop=$scope.'::'.$prop;
        }
        $this->sucker->unset($prop);
    }
    public function & call($target, string $prop, $value_or_args, Proxy $proxy)
    {
        $scope=$this->getScope();
        if($scope!==null){
            $prop=$scope.'::'.$prop;
        }
        return $this->sucker->call($prop,...$value_or_args);
    }
    public function iterator($target,$prop,$value_or_args,Proxy $proxy):\Iterator
    {
        if(is_object($target) && ($target instanceof \IteratorAggregate)){
            return $target->getIterator();
        } 
        if(is_string($target)){
            return new SuckerClassIterator($this->sucker,$this->scope);
        }
        return new SuckerIterator($this->sucker,$this->scope);
    }
}