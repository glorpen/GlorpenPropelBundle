<?php
namespace Glorpen\Propel\PropelBundle\Dispatcher;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class ClassEventDispatcher
{
    protected $dispatchers = array();
    protected $container;
    protected $dispatcherClass = 'Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher';
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function setDispatcherClass($dispatcherClass)
    {
        $this->dispatcherClass = $dispatcherClass;
    }
    
    /**
     * @param string $class
     * @return \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
     */
    public function get($class)
    {
        if (!array_key_exists($class, $this->dispatchers)) {
            $cls = $this->dispatcherClass;
            $this->dispatchers[$class] = new $cls($this->container);
        }
        
        return $this->dispatchers[$class];
    }
    
    public function addListener($class, $eventName, $listener, $priority = 0)
    {
        $this->get($class)->addListener($eventName, $listener, $priority);
    }
    
    public function addListenerService($class, $eventName, $callback, $priority = 0)
    {
        $this->get($class)->addListenerService($eventName, $callback, $priority);
    }
    
    public function addSubscriber($class, EventSubscriberInterface $subscriber)
    {
        $this->get($class)->addSubscriber($subscriber);
    }
    
    public function addSubscriberService($class, $serviceId, $subscriberClass)
    {
        $this->get($class)->addSubscriberService($serviceId, $subscriberClass);
    }
}
