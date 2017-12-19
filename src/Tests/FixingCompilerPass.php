<?php
/**
 * This file is part of the GlorpenPropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license GPLv3
 */

namespace Glorpen\Propel\PropelBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Fixes Propel 1 services behavior in Symfony 4+
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 *
 */
class FixingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('propel.logger')->setPublic(true);
        $container->getDefinition('propel.configuration')->setPublic(true);
    }
}
