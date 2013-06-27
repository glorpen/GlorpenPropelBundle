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
	
	public $commited = false;
	public $rolledback = false;
	
	public function preCommit(\PropelPDO $con = null){
		if($this->transactionError){
			throw new \Exception("some transaction error");
		}
		$this->commited = true;
	}
	public function preRollback(\PropelPDO $con = null){
		$this->rolledback = true;
	}
	
	protected $transactionError = false;
	public function enableTransactionError(){
		$this->transactionError = true;
	}
}
