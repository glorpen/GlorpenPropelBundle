<?php
namespace Glorpen\Propel\PropelBundle\Dispatcher;

use Symfony\Component\EventDispatcher\Event;

/**
 * Simple proxy class, stores callable to dispatcher getter for later usage
 *
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class EventDispatcherProxy
{
    
    static private $dispatcher = null;
    static private $dispatcher_args = array();
    
    /**
     * Sets dispatcher getter
     * @param callable $callback
     * @param array $args
     */
    public static function setDispatcherGetter($callback, $args = array())
    {
        self::$dispatcher = $callback;
        self::$dispatcher_args = $args;
    }
    
    /**
     * Triggers event.
     * @param string $name
     * @param Event $data one of Glorpen\Propel\PropelBundle\Events
     */
    public static function trigger($name, Event $data)
    {
        if (self::$dispatcher) {
            if (!is_array($name)) {
                $name=array($name);
            }
            foreach ($name as $n) {
                call_user_func_array(self::$dispatcher, self::$dispatcher_args)->dispatch($n, $data);
            }
        }
    }
}
