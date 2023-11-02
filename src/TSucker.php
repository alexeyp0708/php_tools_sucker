<?php

namespace Alpa\Tools\Sucker;

/**
 * @deprecated 
 * Trait TSucker
 * This sucker helps to manipulate additional data bound to the class.
 * It also helps to call methods of traits that have been assigned an alias.
 * This approach is necessary in cases where the assigned private method conflicts with the method of the parent class.
 * This sucker only works with private methods of the current class.
 * @package  Alpa\Helpers\Parasites\Sucker
 */
trait TSucker
{
    /**
     * If the parent class implements the __invoke method, then the parent method will be called.
     * In this case, to use the __invoke method of the trait, you must pass the SuckerData object as the first argument.
     * Then the object will execute the specified logic.
     * @param mixed ...$args
     * string $arg[0] - name property/method
     * string $arg[1] - action  `get|set|isset|unset|each|call|sandbox`. Default `get`.
     * last args- arguments depending on action. see action  of Sucker class
     * @return mixed Result depending on the action
     */
    public function __invoke(...$args)
    {
        $parent = get_parent_class($this);
        if ($parent !== false && method_exists($parent, '__invoke') && (count($args) < 1 || !($args[0] instanceof SuckerData))) {
            return parent::__invoke(...$args);
        }
        if ($args instanceof SuckerData) {
            $data = $args[0];
        } else {
            $data = new SuckerData(...$args);
        }
        $sucker = new Sucker($this);
        return $sucker->run($data->action, $data->member, $data->arguments);
    }
}