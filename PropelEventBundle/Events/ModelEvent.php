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

namespace Glorpen\PropelEvent\PropelEventBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use \BaseObject;

/**
 * Event sent form Model object.
 * @author Arkadiusz Dzięgiel
 */
class ModelEvent extends Event {
	private $model;
	
	public function __construct(BaseObject $model){
		$this->model = $model;
	}
	
	public function getModel(){
		return $this->model;
	}
}