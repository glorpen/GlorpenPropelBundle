<?php
namespace Glorpen\PropelEvent\PropelEventBundle\Connection;

use Glorpen\PropelEvent\PropelEventBundle\Events as PropelEvents;
use \PropelPDO;

/**
 * Connection class with transaction events.
 * Can trigger connection.commit and connection.rollback events.
 * @author Arkadiusz Dzięgiel
 */
class EventPropelPDO extends PropelPDO {
	
	public function commit(){
		$opcount = $this->getNestedTransactionCount();
		$return = parent::commit();
		
		if($return && $opcount === 1) EventDispatcherProxy::trigger(new PropelEvents\ConnectionEvent($this), 'connection.commit');
		
		return $return;
	}
	
	public function rollBack(){
		$opcount = $this->getNestedTransactionCount();
		$return = parent::rollBack();
	
		if($return && $opcount === 1) EventDispatcherProxy::trigger(new PropelEvents\ConnectionEvent($this), 'connection.rollback');
	
		return $return;
	}
	
}
