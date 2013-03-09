<?php
namespace Glorpen\Propel\PropelBundle\Connection;

use Glorpen\Propel\PropelBundle\Dispatcher\EventDispatcherProxy;
use Glorpen\Propel\PropelBundle\Events as PropelEvents;
use \PropelPDO;

/**
 * Connection class with transaction events.
 * Can trigger connection.commit and connection.rollback events.
 * @author Arkadiusz Dzięgiel
 */
class EventPropelPDO extends PropelPDO {
	
	public function commit(){
		$opcount = $this->getNestedTransactionCount();
		if($opcount === 1) EventDispatcherProxy::trigger('connection.commit.pre', new PropelEvents\ConnectionEvent($this));
		$return = parent::commit();
		if($return && $opcount === 1) EventDispatcherProxy::trigger('connection.commit.post', new PropelEvents\ConnectionEvent($this));
		var_dump($return);
		return $return;
	}
	
	public function rollBack(){
		$opcount = $this->getNestedTransactionCount();
		if($opcount === 1) EventDispatcherProxy::trigger('connection.rollback.pre', new PropelEvents\ConnectionEvent($this));
		$return = parent::rollBack();
		if($return && $opcount === 1) EventDispatcherProxy::trigger('connection.rollback.post', new PropelEvents\ConnectionEvent($this));
	
		return $return;
	}
	
}
