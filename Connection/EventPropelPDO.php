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
class EventPropelPDO extends PropelPDO
{
    
    public function beginTransaction()
    {
        $opcount = $this->getNestedTransactionCount();
        if ($opcount === 0) {
            EventDispatcherProxy::trigger('connection.begin.pre', new PropelEvents\ConnectionEvent($this));
        }
        $ret = parent::beginTransaction();
        if ($ret && $opcount === 0) {
            EventDispatcherProxy::trigger('connection.begin.post', new PropelEvents\ConnectionEvent($this));
        }
        return $ret;
    }
    
    public function commit()
    {
        $opcount = $this->getNestedTransactionCount();
        if ($opcount === 1) {
            EventDispatcherProxy::trigger('connection.commit.pre', new PropelEvents\ConnectionEvent($this));
        }
        $return = parent::commit();
        if ($return && $opcount === 1) {
            EventDispatcherProxy::trigger('connection.commit.post', new PropelEvents\ConnectionEvent($this));
        }
        
        return $return;
    }
    
    public function rollBack()
    {
        $opcount = $this->getNestedTransactionCount();
        if ($opcount === 1) {
            EventDispatcherProxy::trigger('connection.rollback.pre', new PropelEvents\ConnectionEvent($this));
        }
        $return = parent::rollBack();
        if ($return && $opcount === 1) {
            EventDispatcherProxy::trigger('connection.rollback.post', new PropelEvents\ConnectionEvent($this));
        }
    
        return $return;
    }
    
    public function __construct($dsn, $username = null, $password = null, $driver_options = array())
    {
        parent::__construct($dsn, $username, $password, $driver_options);
        EventDispatcherProxy::trigger('connection.create', new PropelEvents\ConnectionEvent($this));
    }
}
