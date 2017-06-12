<?php

namespace Glorpen\Propel\PropelBundle\Tests;

use Glorpen\Propel\PropelBundle\Dispatcher\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Glorpen\Propel\PropelBundle\Dispatcher\ClassEventDispatcher;
use Glorpen\Propel\PropelBundle\Events\ModelEvent;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Glorpen\Propel\PropelBundle\Events\QueryEvent;
use Glorpen\Propel\PropelBundle\Events\PeerEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Arkadiusz Dzięgiel
 */
class EventDispatcherTest extends TestCase
{
    
    protected function assertClassEvent($event, $eventName, $class)
    {
        $genericDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher')
            ->disableOriginalConstructor()->getMock();
        $classDispatcher = $this->getMockBuilder('Glorpen\Propel\PropelBundle\Dispatcher\ClassEventDispatcher')
            ->disableOriginalConstructor()->getMock();
        
        $genericDispatcher->expects($this->once())->method('dispatch')->with($eventName, $event);
        $classDispatcher->expects($this->once())->method('get')->with($class)->willReturn($genericDispatcher);
        
        $dispatcher = new EventDispatcher($classDispatcher);
        
        $dispatcher->dispatch($eventName, $event);
    }
    
    public function testClassEvents()
    {
        $modelEvent = $this->getMockBuilder('Glorpen\Propel\PropelBundle\Events\ModelEvent')
            ->disableOriginalConstructor()->getMock();
        $modelEvent->expects($this->once())->method('getModel')->willReturn($this);
        
        $this->assertClassEvent($modelEvent, 'model.insert.pre', get_class($this));
        
        $queryEvent = $this->getMockBuilder('Glorpen\Propel\PropelBundle\Events\QueryEvent')
            ->disableOriginalConstructor()->getMock();
        $queryEvent->expects($this->once())->method('getQuery')->willReturn($this);
        
        $this->assertClassEvent($queryEvent, 'query.delete.pre', get_class($this));
        
        $peerEvent = $this->getMockBuilder('Glorpen\Propel\PropelBundle\Events\PeerEvent')
            ->disableOriginalConstructor()->getMock();
        $peerEvent->expects($this->once())->method('getClass')->willReturn('SomeClass');
        
        $this->assertClassEvent($peerEvent, 'peer.construct', 'SomeClass');
    }
}
