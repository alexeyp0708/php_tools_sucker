<?php


namespace Alpa\Tools\Sucker;


interface SuckerHandlersInterface
{
    public function setSubject($subject):void;
    
    public function getSubject($subject);

    public function setScope(?string $class):self;  
    
    public function & get(string $member);
    
    public function set(string $member,&$value):void;
    
    public function & call (string $member,&...$args);
    
    public function each(callable $each):void;
    
    public function isset(string $member):bool;
    
    public function unset(string $member):void;
    
    public function & sandbox(callable $call,$args);
}