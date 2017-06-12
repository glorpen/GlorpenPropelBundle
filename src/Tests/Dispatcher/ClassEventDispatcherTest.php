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
        $dispatcher = new ClassEventDispatcher();
        
        $dispatcher->addListener('testClass', 'testEvent1', 'listenerTest');
        $dispatcher->addSubscriber('testClass', $this);
        
        $d = $dispatcher->get('testClass');
        
        $this->assertContains('listenerTest', $d->getListeners('testEvent1'));
        $this->assertContains(array($this, 'subscriberMethod'), $d->getListeners('subscriberEvent'));
    }
    
    public static function getSubscribedEvents()
    {
        return array('subscriberEvent' => 'subscriberMethod');
    }
}
