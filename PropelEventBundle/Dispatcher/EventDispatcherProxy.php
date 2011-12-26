<?php
namespace Glorpen\PropelEvent\PropelEventBundle\Dispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\EventDispatcher\Event;

class EventDispatcherProxy {
	
	static private $dispatcher = null;
	static private $dispatcher_args=array();
	
	static public function setDispatcherGetter($callback, $args=array()){
		self::$dispatcher = $callback;
		self::$dispatcher_args = $args;
	}
	
	static public function trigger($name, Event $data){
		if(self::$dispatcher) {
			call_user_func_array(self::$dispatcher, self::$dispatcher_args)->dispatch($name, $data);
		}
	}
}