<?php
namespace Glorpen\Propel\PropelBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use \PropelPDO;

/**
 * Event sent form PropelPDO.
 * @author Arkadiusz Dzięgiel
 */
class ConnectionEvent extends Event
{
    private $connection;
    
    public function __construct(PropelPDO $connection)
    {
        $this->connection = $connection;
    }
    
    public function getConnection()
    {
        return $this->connection;
    }
}
