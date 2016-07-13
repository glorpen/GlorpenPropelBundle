<?php

namespace Glorpen\Propel\PropelBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class PropelOMPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('glorpen.propel.listeners.om_overrider')) {
            return;
        }
        
        $definition = $container->getDefinition('glorpen.propel.listeners.om_overrider');

        foreach ($container->findTaggedServiceIds('propel.om') as $id => $tags) {
            $definition->addMethodCall('addProvider', array(new Reference($id)));
        }
    }
}
