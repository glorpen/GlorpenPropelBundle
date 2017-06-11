<?php

namespace Glorpen\Propel\PropelBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class PropelEventPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('glorpen.propel.event.dispatcher')
            || !$container->hasDefinition('glorpen.propel.event.class_dispatcher')
        ) {
            return;
        }
        
        $mainDefinition = $container->getDefinition('glorpen.propel.event.dispatcher');
        $classDefinition = $container->getDefinition('glorpen.propel.event.class_dispatcher');
        
        foreach ($container->findTaggedServiceIds('propel.event') as $id => $tags) {
            foreach ($tags as $tag) {
                $isListener = !empty($tag['method']) && !empty($tag['event']);
                $isClass = !empty($tag['class']);
                
                if ($isListener) {
                    $priority = (int) @$tag['priority'];
                    if ($isClass) {
                        $classDefinition->addMethodCall(
                            'addListenerService',
                            array($tag['class'], $tag['event'], array($id, $tag['method']), $priority)
                        );
                    } else {
                        $mainDefinition->addMethodCall(
                            'addListenerService',
                            array($tag['event'], array($id, $tag['method']), $priority)
                        );
                    }
                } else {
                    $subscriberClass = $container->getDefinition($id)->getClass();
                    
                    if ($subscriberClass) {
                        if ($isClass) {
                            $classDefinition->addMethodCall(
                                'addSubscriberService',
                                array($tag['class'], $id, $subscriberClass)
                            );
                        } else {
                            $mainDefinition->addMethodCall('addSubscriberService', array($id, $subscriberClass));
                        }
                    } else {
                        if ($isClass) {
                            $classDefinition->addMethodCall('addSubscriber', array($tag['class'], new Reference($id)));
                        } else {
                            $mainDefinition->addMethodCall('addSubscriber', array(new Reference($id)));
                        }
                    }
                }
            }
        }
    }
}
