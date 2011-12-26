<?php
namespace Glorpen\PropelEvent\PropelEventBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use \PropelPDO;

class ConnectionEvent extends Event {
	private $connection;
	
	public function __construct(PropelPDO $connection){
		$this->connection = $connection;
	}
	
	public function getConnection(){
		return $this->connection;
	}
}