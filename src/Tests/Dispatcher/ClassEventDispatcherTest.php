<?php

namespace Glorpen\Propel\PropelBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Glorpen\Propel\PropelBundle\Dispatcher\ClassEventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Arkadiusz Dzięgiel
 */
class ClassEventDispatcherTest extends TestCase implements EventSubscriberInterface
{
    public function testListeners()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMockForAbstractClass();
        $dispatcher = new ClassEventDispatcher($container);
        
        $container->method('get')->withConsecutive(array('testId1',1), array('testId2', 1))->willReturn($this);
        
        $dispatcher->addListener('testClass', 'testEvent1', 'listenerTest');
        $event2Listener = $dispatcher->addListenerService('testClass', 'testEvent2', array('testId1','testMethod'));
        $dispatcher->addSubscriber('testClass', $this);
        
        $d = $dispatcher->get('testClass');
        
        $this->assertContains('listenerTest', $d->getListeners('testEvent1'));
        $this->assertContains($event2Listener, $d->getListeners('testEvent2'));
        $this->assertContains(array($this, 'subscriberMethod'), $d->getListeners('subscriberEvent'));
        
        $class2Listeners = $dispatcher->addSubscriberService('testClass2', 'testId2', get_class($this));
        $this->assertContains(
            $class2Listeners[0],
            $dispatcher->get('testClass2')->getListeners('subscriberEvent')
        );
    }
    
    public static function getSubscribedEvents()
    {
        return array('subscriberEvent' => 'subscriberMethod');
    }
}
