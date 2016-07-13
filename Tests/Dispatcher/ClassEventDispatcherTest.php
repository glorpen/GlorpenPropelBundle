<?php

namespace Glorpen\Propel\PropelBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Glorpen\Propel\PropelBundle\Dispatcher\ClassEventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Arkadiusz Dzięgiel
 */
class ClassEventDispatcherTest extends \PHPUnit_Framework_TestCase implements EventSubscriberInterface
{
    public function testListeners()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $dispatcher = new ClassEventDispatcher($container);
        
        $container->method('get')->withConsecutive(array('testId1',1), array('testId2', 1))->willReturn($this);
        
        $dispatcher->addListener('testClass', 'testEvent1', 'listenerTest');
        $dispatcher->addListenerService('testClass', 'testEvent2', array('testId1','testMethod'));
        $dispatcher->addSubscriber('testClass', $this);
        
        $d = $dispatcher->get('testClass');
        
        $this->assertContains('listenerTest', $d->getListeners('testEvent1'));
        $this->assertContains(array($this, 'testMethod'), $d->getListeners('testEvent2'));
        $this->assertContains(array($this, 'subscriberMethod'), $d->getListeners('subscriberEvent'));
        
        $dispatcher->addSubscriberService('testClass2', 'testId2', get_class($this));
        $this->assertContains(
            array($this, 'subscriberMethod'),
            $dispatcher->get('testClass2')->getListeners('subscriberEvent')
        );
    }
    
    public static function getSubscribedEvents()
    {
        return array('subscriberEvent' => 'subscriberMethod');
    }
}
