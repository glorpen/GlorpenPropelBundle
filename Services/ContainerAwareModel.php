<?php

namespace Glorpen\Propel\PropelBundle\Services;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Glorpen\Propel\PropelBundle\Events\ModelEvent;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Arkadiusz Dzięgiel
 */
class ContainerAwareModel {
	
	protected $container;
	
	public function __construct(ContainerInterface $container){
		$this->container = $container;
	}
	
	public function onModelConstruct(ModelEvent $event){
		$m = $event->getModel();
		if($m instanceof ContainerAwareInterface){
			$m->setContainer($this->container);
		}
	}
}
