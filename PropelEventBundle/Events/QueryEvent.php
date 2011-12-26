<?php
namespace Glorpen\PropelEvent\PropelEventBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use \ModelCriteria;

class QueryEvent extends Event {
	private $query;
	
	public function __construct(ModelCriteria $query){
		$this->query = $query;
	}
	
	public function getQuery(){
		return $this->query;
	}
}