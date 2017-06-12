<?php
namespace Glorpen\Propel\PropelBundle\Tests\Fixtures\Services;

/**
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class EventTester
{
    public $arg1;
    public $handledEvent;
    
    public function __construct($arg1 = null)
    {
        $this->arg1=$arg1;
    }
    
    public function handleEvent($event)
    {
        $this->handledEvent = $event;
    }
}
