<?php

namespace Glorpen\Propel\PropelBundle\Tests;

use Glorpen\Propel\PropelBundle\Dispatcher\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Glorpen\Propel\PropelBundle\Dispatcher\ClassEventDispatcher;
use Glorpen\Propel\PropelBundle\Events\ModelEvent;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Glorpen\Propel\PropelBundle\Events\QueryEvent;
use Glorpen\Propel\PropelBundle\Events\PeerEvent;

/**
 * @author Arkadiusz Dzięgiel
 */
class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    
    protected function assertClassEvent($event, $eventName, $class)
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $genericDispatcher = $this->getMockBuilder(ContainerAwareEventDispatcher::class)->disableOriginalConstructor()->getMock();
        $classDispatcher = $this->getMockBuilder(ClassEventDispatcher::class)->disableOriginalConstructor()->getMock();
        
        $genericDispatcher->expects($this->once())->method('dispatch')->with($eventName, $event);
        $classDispatcher->expects($this->once())->method('get')->with($class)->willReturn($genericDispatcher);
        
        $dispatcher = new EventDispatcher($container, $classDispatcher);
        
        $dispatcher->dispatch($eventName, $event);
    }
    
    public function testClassEvents()
    {
        $modelEvent = $this->getMockBuilder(ModelEvent::class)->disableOriginalConstructor()->getMock();
        $modelEvent->expects($this->once())->method('getModel')->willReturn($this);
        
        $this->assertClassEvent($modelEvent, 'model.insert.pre', get_class($this));
        
        $queryEvent = $this->getMockBuilder(QueryEvent::class)->disableOriginalConstructor()->getMock();
        $queryEvent->expects($this->once())->method('getQuery')->willReturn($this);
        
        $this->assertClassEvent($queryEvent, 'query.delete.pre', get_class($this));
        
        $peerEvent = $this->getMockBuilder(PeerEvent::class)->disableOriginalConstructor()->getMock();
        $peerEvent->expects($this->once())->method('getClass')->willReturn('SomeClass');
        
        $this->assertClassEvent($peerEvent, 'peer.construct', 'SomeClass');
    }
}
