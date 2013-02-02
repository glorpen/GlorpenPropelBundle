<?php
/***
 * GlorpenPropelBundle provides additional integration with Propel to Symfony2.
 * Copyright (C) 2013  Arkadiusz Dzięgiel
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

namespace Glorpen\Propel\PropelBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use Glorpen\Propel\PropelBundle\Dispatcher\EventDispatcherProxy;

use Glorpen\Propel\PropelBundle\DependencyInjection\Compiler\PropelEventPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Arkadiusz Dzięgiel
 */
class GlorpenPropelBundle extends Bundle
{
	
	public function build(ContainerBuilder $container)
	{
		$container->addCompilerPass(new PropelEventPass());
	}
	
	public function boot()
	{
		// set callback in proxy dispatcher,
		// so it can later get real dispatcher from container
		EventDispatcherProxy::setDispatcherGetter(array($this->container, 'get'), array('glorpen.propel.event.dispatcher'));
	}
	
}
