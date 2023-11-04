<?php

namespace Alpa\Tools\Sucker;

use Alpa\Tools\ProxyObject\Handlers\ActionsInterface;
use Alpa\Tools\ProxyObject\Handlers\InstanceActions;
use Alpa\Tools\ProxyObject\ProxyInterface;

class SuckerProxyHandlers extends Sucker implements ActionsInterface
{

    public function & run(string $action, $target, ?string $prop, $value_or_arguments, ProxyInterface $proxy)
    {
        $answer = null;
        switch ($action) {
            case 'get':
                $answer = &$this->get($prop);
                break;
            case 'set':
                $this->set($prop, $value_or_arguments);
                break;
            case 'isset':
                $answer = $this->isset($prop);
                break;
            case 'unset':
                $this->unset($prop);
                break;
            case 'iterator':
                $answer = $this->iterator($target);
                break;
            case 'toString':
                $answer = $this->toString($target);
                break;
            case 'call':
                $answer = &$this->call($prop, ...$value_or_arguments);
                break;
            case 'invoke':
                $answer = $this($value_or_arguments);
                break;
        }
        return $answer;
    }

    public static function & static_run(string $action, $target, ?string $prop, $value_or_args, ProxyInterface $proxy)
    {
        // TODO: Implement static_run() method.
    }

    private function toString($target): string
    {
        return $target . '';
    }

    private function iterator($target): \Iterator
    {
        /*if (is_object($target) && ($target instanceof \IteratorAggregate)) {
            // пересать в комбинации с Итреатором Прокси
            return $target->getIterator();
        }*/
        if (is_string($target)) {
            return new SuckerClassIterator($this, $this->getScope());
        }
        return new SuckerIterator($this, $this->getScope());
    }
}