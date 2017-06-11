<?php
namespace Glorpen\Propel\PropelBundle\Dispatcher;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class ClassEventDispatcher
{
    protected $dispatchers = array();
    protected $container;
    protected $dispatcherClass = 'Glorpen\Propel\PropelBundle\Dispatcher\CompatEventDispatcher';
    
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function setDispatcherClass($dispatcherClass)
    {
        $this->dispatcherClass = $dispatcherClass;
    }
    
    protected function isServiceInjectionSupported()
    {
        $r = new \ReflectionClass($this->dispatcherClass);
        return $r->hasMethod('addListenerService') && $r->hasMethod('addSubscriberService');
    }
    
    protected function assertServiceInjectionSupport()
    {
        if (!$this->isServiceInjectionSupported()) {
            throw new \RuntimeException(sprintf('Service injection is not supported for %s', $this->dispatcherClass));
        }
    }
    
    /**
     * @param string $class
     * @return \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
     */
    public function get($class)
    {
        if (!array_key_exists($class, $this->dispatchers)) {
            $cls = $this->dispatcherClass;
            $dispatcher = new $cls();
            if ($dispatcher instanceof ContainerAwareInterface) {
                $dispatcher->setContainer($this->container);
            }
            $this->dispatchers[$class] = $dispatcher;
        }
        
        return $this->dispatchers[$class];
    }
    
    public function addListener($class, $eventName, $listener, $priority = 0)
    {
        $this->get($class)->addListener($eventName, $listener, $priority);
    }
    
    public function addSubscriber($class, EventSubscriberInterface $subscriber)
    {
        $this->get($class)->addSubscriber($subscriber);
    }
    
    public function addListenerService($class, $eventName, $callback, $priority = 0)
    {
        $this->assertServiceInjectionSupport();
        return $this->get($class)->addListenerService($eventName, $callback, $priority);
    }
    
    public function addSubscriberService($class, $serviceId, $subscriberClass)
    {
        $this->assertServiceInjectionSupport();
        return $this->get($class)->addSubscriberService($serviceId, $subscriberClass);
    }
}
