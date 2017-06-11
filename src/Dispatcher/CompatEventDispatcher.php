<?php
namespace Glorpen\Propel\PropelBundle\Dispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Compatibility class.
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 *
 */
class CompatEventDispatcher extends SymfonyEventDispatcher implements ContainerAwareInterface
{
    protected $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    protected function assertContainer()
    {
        if ($this->container === null) {
            throw new \RuntimeException('Trying to use id based listener when container is not set - use closure or set container');
        }
    }
    
    protected function getClosure($serviceId, $method)
    {
        return function() use ($serviceId, $method) {
            $service = $this->container->get($serviceId);
            return call_user_func(array($service, $method));
        };
    }
    
    public function addListenerService($eventName, $callback, $priority = 0)
    {
        $this->assertContainer();
        
        if (!is_array($callback) || 2 !== count($callback)) {
            throw new \InvalidArgumentException('Expected an array("service", "method") argument');
        }
        
        $serviceId = $callback[0];
        $method = $callback[1];
        
        $closure = $this->getClosure($serviceId, $method);
        $this->addListener($eventName, $closure, $priority);
        return $closure;
    }
    
    public function addSubscriberService($serviceId, $class)
    {
        $closures = array();
        
        foreach ($class::getSubscribedEvents() as $eventName => $params) {
            foreach ($class::getSubscribedEvents() as $eventName => $params) {
                if (is_string($params)) {
                    $closures[] = $this->addListenerService($eventName, array($serviceId, $params), 0);
                } elseif (is_string($params[0])) {
                    $closures[] = $this->addListenerService($eventName, array($serviceId, $params[0]), isset($params[1]) ? $params[1] : 0);
                } else {
                    foreach ($params as $listener) {
                        $closures[] = $this->addListenerService($eventName, array($serviceId, $listener[0]), isset($listener[1]) ? $listener[1] : 0);
                    }
                }
            }
        }
        
        return $closures;
    }
}
