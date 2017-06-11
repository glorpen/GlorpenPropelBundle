<?php
namespace Glorpen\Propel\PropelBundle\Dispatcher;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Glorpen\Propel\PropelBundle\Events\ModelEvent;
use Glorpen\Propel\PropelBundle\Events\QueryEvent;
use Glorpen\Propel\PropelBundle\Events\PeerEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class EventDispatcher extends ContainerAwareEventDispatcher
{
    
    protected $classDispatcher;
    
    public function __construct(ContainerInterface $container, ClassEventDispatcher $classDispatcher)
    {
        parent::__construct($container);
        $this->classDispatcher = $classDispatcher;
    }
    
    public function dispatch($eventName, Event $event = null)
    {
        if ($event) {
            $this->dispatchClassEvent($eventName, $event);
        }
        return parent::dispatch($eventName, $event);
    }
    
    protected function dispatchClassEvent($eventName, Event $event)
    {
        if ($event instanceof ModelEvent) {
            $class = get_class($event->getModel());
        } elseif ($event instanceof QueryEvent) {
            $class = get_class($event->getQuery());
        } elseif ($event instanceof PeerEvent) {
            $class = $event->getClass();
        } else {
            return;
        }
        
        $this->classDispatcher->get($class)->dispatch($eventName, $event);
    }
}
