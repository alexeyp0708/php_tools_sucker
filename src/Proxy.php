<?php

namespace Alpa\Tools\Sucker;

use Alpa\Tools\ProxyObject\Proxy as CoreProxy;

final class Proxy extends CoreProxy
{
    public function __construct($target, string $handlers = SuckerProxyHandlers::class)
    {
        if (!is_subclass_of($handlers, HandlersInterface::class)
        ) {
            throw new \Exception('argument 2: the object must implement interface' . HandlersInterface::class .
                ', or if class name, then the class must implement interface ' . HandlersInterface::class);
        }
        $handlers = new $handlers($target);
        parent::__construct($target, $handlers);
    }

    /**
     * @inheritDoc
     */
    protected function & run(string $action, ?string $prop = null, $value_or_arguments = null)
    {
        $result = &parent::run($action, $prop, $value_or_arguments);
        return $result;
    }

    /**
     * Calling a proxy object to set the scope.
     * @param $scope null|string|\Closure  The scope of the object.  If a closure function is passed, then this function will be called as a sandbox.
     * @return $this
     */
    public function & __invoke(...$args)
    {
        if ($args[0] instanceof \Closure) {
            return $this->handlers->sandbox(...$args);
        }
        $scope = $args[0];
        $this->handlers->setScope($scope);
        return $this;

        //return parent::__invoke($arguments); // TODO: Change the autogenerated stub
    }
}