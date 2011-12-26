<?php
namespace Glorpen\PropelEvent\PropelEventBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use \BaseObject;

class ModelEvent extends Event {
	private $model;
	
	public function __construct(BaseObject $model){
		$this->model = $model;
	}
	
	public function getModel(){
		return $this->model;
	}
}