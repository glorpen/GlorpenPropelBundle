<?php

/**
 * This file is part of the GlorpenPropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license GPLv3
 */

namespace Glorpen\Propel\PropelBundle\Tests\Fixtures;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
                new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                new \Propel\PropelBundle\PropelBundle(),
                new \Glorpen\Propel\PropelBundle\GlorpenPropelBundle(),
        );
    
        return $bundles;
    }
    
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(realpath(__DIR__.'/../Resources/config').'/config_'.$this->getEnvironment().'.yml');
    }
}
