<?php
/**
 * This file is part of the GlorpenPropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license GPLv3
 */

namespace Glorpen\Propel\PropelBundle\Tests;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Filesystem\Filesystem;

class TestKernel extends Kernel
{
    protected $debug = true;
    
    private $containerBuilder;
    
    static private $nameSeed = 0;
    
    public function getName()
    {
        if (null === $this->name) {
            self::$nameSeed++;
            $this->name = 'test_'.self::$nameSeed;
        }
        
        return $this->name;
    }
    
    public function getCacheDir()
    {
        return parent::getCacheDir().'/'.$this->getName();
    }
    
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
        $loader->load(realpath(__DIR__.'/Resources/config').'/config_'.$this->getEnvironment().'.yml');
    }
    
    public function getRootDir()
    {
        return realpath(__DIR__.'/../..').'/test-app';
    }
    
    /**
     * For symfony 3.3
     * @param ContainerBuilder $container
     */
    protected function build(ContainerBuilder $container)
    {
        if ($this->containerBuilder) {
            call_user_func($this->containerBuilder, $container);
        }
    }
    
    public function setContainerBuilder($callback)
    {
        $this->containerBuilder = $callback;
    }
    
    /**
     * For symfony 2.3
     * @param ContainerBuilder $container
     */
    protected function prepareContainer(ContainerBuilder $container)
    {
        parent::prepareContainer($container);
        $this->build($container);
    }
    
    public function shutdown()
    {
        parent::shutdown();
        $fs = new Filesystem();
        $fs->remove($this->getCacheDir());
    }
}
