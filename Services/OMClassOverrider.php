<?php

namespace Glorpen\Propel\PropelBundle\Services;

use Glorpen\Propel\PropelBundle\Events\DetectOMClassEvent;

use Glorpen\Propel\PropelBundle\Events\PeerEvent;

use Glorpen\Propel\PropelBundle\Provider\OMClassProvider;

/**
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class OMClassOverrider
{
    
    private $classes = array();
    private $providers = array();
    private $dynamicClasses = array();
    
    public function __construct(array $map)
    {
        $this->classes = $map;
    }
    
    public function addProvider(OMClassProvider $provider)
    {
        foreach ($provider->getMapping() as $cls => $baseClass) {
            if (!array_key_exists($baseClass, $this->providers)) {
                $this->providers[$baseClass] = array();
            }
            $this->providers[$baseClass][] = $provider;
            if (array_key_exists($cls, $this->dynamicClasses)) {
                throw new \RuntimeException("Class $cls is already mapped");
            }
            $this->dynamicClasses[$cls] = $baseClass;
        }
    }
    
    public function getClassForOM($omClass, $row = 0, $col = 0)
    {
        if (array_key_exists($omClass, $this->providers)) {
            foreach ($this->providers[$omClass] as $provider) {
                $ret = $provider->getOMClass($row, $col);
                if ($ret !== null) {
                    return $ret;
                }
            }
        }
        if (array_key_exists($omClass, $this->classes)) {
            return $this->classes[$omClass];
        } else {
            return null;
        }
    }
    
    /**
     * Returns base class
     * @param string $class
     * @return string
     */
    public function getExtendedClass($class)
    {
        if (array_key_exists($class, $this->dynamicClasses)) {
            $ret = $this->dynamicClasses[$class];
        }
        if (!$ret) {
            $ret = array_search($class, $this->classes);
        }
        return $ret?$ret:null;
    }
    
    public function onDetectionRequest(DetectOMClassEvent $e)
    {
        if (!$e->isDetected()) {
            $e->setDetectedClass($this->getClassForOM($e->getClass(), $e->getRow(), $e->getCol()));
        }
    }
}
