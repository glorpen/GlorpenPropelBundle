<?php

namespace Glorpen\Propel\PropelBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;

/**
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class PropelEventPass implements CompilerPassInterface
{

    static public function isClosureSupported()
    {
        return class_exists('Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument');
    }
    
    protected function addListeners($definition, $id, $method, $priority, array $baseArgs, $isClosureSupported, $isPublic)
    {
        if ($isClosureSupported) {
            $definition->addMethodCall('addListener', array_merge($baseArgs, array(
                array(
                    new ServiceClosureArgument(new Reference($id)),
                    $method
                ),
                $priority
            )));
        } else {
            if ($isPublic) {
                $definition->addMethodCall('addListenerService', array_merge($baseArgs, array(
                    array(
                        $id,
                        $method
                    ),
                    $priority
                )));
            } else {
                $definition->addMethodCall('addListener', array_merge($baseArgs, array(
                    array(
                        new Reference($id),
                        $method
                    ),
                    $priority
                )));
            }
        }
    }
    
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('glorpen.propel.event.dispatcher')
            || !$container->hasDefinition('glorpen.propel.event.class_dispatcher')
        ) {
            return;
        }
        
        $mainDefinition = $container->getDefinition('glorpen.propel.event.dispatcher');
        $classDefinition = $container->getDefinition('glorpen.propel.event.class_dispatcher');
        
        $isClosureSupported = self::isClosureSupported();
        
        foreach ($container->findTaggedServiceIds('propel.event') as $id => $tags) {
            
            $isPublic = $container->getDefinition($id)->isPublic();
            
            foreach ($tags as $tag) {
                $isListener = !empty($tag['method']) && !empty($tag['event']);
                $isClass = !empty($tag['class']);
                
                if ($isListener) {
                    $priority = (int) @$tag['priority'];
                    
                    if ($isClass) {
                        $this->addListeners($classDefinition, $id, $tag['method'], $priority, array(
                            $tag['class'],
                            $tag['event']
                        ), $isClosureSupported, $isPublic);
                    } else {
                        $this->addListeners($mainDefinition, $id, $tag['method'], $priority, array(
                            $tag['event']
                        ), $isClosureSupported, $isPublic);
                    }
                } else {
                    
                    $subscriberClass = $container->getDefinition($id)->getClass();
                    if ($subscriberClass && $isPublic && !$isClosureSupported) {
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
