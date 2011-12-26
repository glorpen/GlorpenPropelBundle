<?php

namespace Glorpen\PropelEvent\PropelEventBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class PropelEventPass implements CompilerPassInterface
{
    protected $channels = array();

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('glorpen.propel.event.dispatcher')) {
            return;
        }
        
		$definition = $container->getDefinition('glorpen.propel.event.dispatcher');
		
        foreach ($container->findTaggedServiceIds('propel.event') as $id => $tags) {
            foreach ($tags as $tag) {
                if (!empty($tag['method'])) {
                	$definition->addMethodCall('addListenerService', array($id, $tag['method']));
                } else {
                	$definition->addMethodCall('addSubscriber', array(new Reference($id)));
                }
            }
        }
    }
}
