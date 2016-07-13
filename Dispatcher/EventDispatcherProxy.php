<?php
/***
* PropelEventBundle provides dynamic class extending.
* Copyright (C) 2011  Arkadiusz Dzięgiel
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Glorpen\Propel\PropelBundle\Dispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\EventDispatcher\Event;

/**
 * Simple proxy class, stores callable to dispatcher getter for later usage
 *
 * @author Arkadiusz Dzięgiel
 */
class EventDispatcherProxy
{
    
    static private $dispatcher = null;
    static private $dispatcher_args=array();
    
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
     * @param Event $data one of Glorpen\Propel\PropelBundle\\Events
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
