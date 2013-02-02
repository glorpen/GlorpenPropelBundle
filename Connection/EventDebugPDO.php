<?php
namespace Glorpen\Propel\PropelBundle\Connection;

/**
 * Connection class with transaction events.
 * Can trigger connection.commit and connection.rollback events.
 * @author Arkadiusz Dzięgiel
 */
class EventDebugPDO extends EventPropelPDO {
	
	public $useDebug = true;
	
}
