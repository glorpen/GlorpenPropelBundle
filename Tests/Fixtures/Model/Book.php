<?php
namespace Glorpen\Propel\PropelBundle\Tests\Fixtures\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\om\BaseBook;

class Book extends BaseBook implements ContainerAwareInterface {
	
	protected $container;
	
	public function setContainer(ContainerInterface $c = null){
		$this->container = $c;
	}
	
	public function hasContainer(){
		return $this->container !== null;
	}
}
