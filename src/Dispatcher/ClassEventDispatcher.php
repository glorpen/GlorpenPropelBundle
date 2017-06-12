<?php
namespace Glorpen\Propel\PropelBundle\Dispatcher;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class ClassEventDispatcher
{
    protected $dispatchers = array();
    protected $dispatcherClass = 'Symfony\Component\EventDispatcher\EventDispatcher';
    
    public function setDispatcherClass($dispatcherClass)
    {
        $this->dispatcherClass = $dispatcherClass;
    }
    
    /**
     * @param string $class
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    public function get($class)
    {
        if (!array_key_exists($class, $this->dispatchers)) {
            $cls = $this->dispatcherClass;
            $this->dispatchers[$class] = new $cls();
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
}
