<?php

namespace Glorpen\Propel\PropelBundle\Services;

use Glorpen\Propel\PropelBundle\Events\DetectOMClassEvent;

use Glorpen\Propel\PropelBundle\Events\PeerEvent;

use Glorpen\Propel\PropelBundle\Provider\OMClassProvider;

class OMClassOverrider {
	
	private $classes = array();
	private $providers = array();
	
	public function __construct(array $map){
		$this->classes = $map;
	}
	
	public function addProvider(OMClassProvider $provider){
		foreach($provider->getSubscribedClasses() as $cls){
			if(!array_key_exists($cls, $this->providers)) $this->providers[$cls] = array();
			$this->providers[$cls][] = $provider;
		}
	}
	
	public function getClassForOM($omClass, $row = 0, $col = 0){
		if(array_key_exists($omClass, $this->providers)){
			foreach($this->providers[$omClass] as $provider){
				$ret = $provider->getOMClass($row, $col);
				if($ret !== null) return $ret;
			}
		}
		if(array_key_exists($omClass, $this->classes)){
			return $this->classes[$omClass];
		} else {
			return null;
		}
	}
	
	public function onDetectionRequest(DetectOMClassEvent $e){
		if(!$e->isDetected()){
			$e->setDetectedClass($this->getClassForOM($e->getClass(), $e->getRow(), $e->getCol()));
		}
	}
}
