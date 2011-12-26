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

namespace Glorpen\PropelEvent\PropelEventBundle;

use Glorpen\PropelEvent\PropelEventBundle\Dispatcher\EventDispatcherProxy;

use Glorpen\PropelEvent\PropelEventBundle\DependencyInjection\Compiler\PropelEventPass;

use Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PropelEventBundle extends Bundle
{
	
    public function build(ContainerBuilder $container)
    {
    	parent::build($container);
    
    	$container->addCompilerPass(new PropelEventPass());
    }
    
    public function boot()
    {
    	// set callback in proxy dispatcher,
    	// so it can later get real dispatcher from container
    	EventDispatcherProxy::setDispatcherGetter(array($this->container, 'get'), array('glorpen.propel.event.dispatcher'));
    }
}
