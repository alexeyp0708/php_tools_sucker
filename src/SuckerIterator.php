<?php

namespace Alpa\Tools\Sucker;

class SuckerIterator implements \Iterator
{
    private Sucker $sucker;
    private ?string $scope;
    private int $key=0;
    protected array $props=[];
    public function __construct(Sucker $sucker,string $scope=null)
    {
        $this->sucker=$sucker;
        $this->scope=$scope;
        //$this->rewind();
    }
    private function getProps():array
    {
        return $this->sucker->sandbox(function (){
            return array_keys(get_object_vars($this));
        },$this->scope);
    }
    public function rewind():void
    {
        $this->key=0;
        $this->props=$this->getProps();
    }
    public function key()
    {
        return $this->props[$this->key]??null;
    }
    public function next():void
    {
        do {
            $this->key++;
        } while (isset($tgis->props[$this->key]) && !$this->sucker->sandbox(function($prop){
            return property_exists($this,$prop);
        },$this->scope,$this->key()));
    }
    public function valid():bool
    {
        $prop=$this->key();
        if($prop===null){
            $diff=array_values(array_diff($this->getProps(),$this->props));
            if(count($diff)>0){
                $this->props=$diff;
                $this->rewind();
                return true;
            }
            return false;
        }
        return true; 
    }

    public function current()
    {
        return $this->sucker->sandbox(function($prop){
            return $this->$prop;
        },$this->scope,[$this->key()]);
    }
}