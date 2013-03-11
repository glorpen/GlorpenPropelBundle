<?php

namespace Glorpen\Propel\PropelBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Arkadiusz Dzięgiel
 */
class GlorpenPropelExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
    	
    	$processor = new Processor();
    	$configuration = new Configuration();
    	
    	$config = $processor->processConfiguration($configuration, $configs);
    	
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        
        $this->setupOverrider($config, $container);
    }
    
    public function setupOverrider(array $config, ContainerBuilder $container){
    	$container->getDefinition("glorpen.propel.listeners.om_overrider")
    		->addArgument($config['extended_models']);
    }
    
    public function getAlias(){
    	return 'glorpen_propel';
    }
}
