<?php

namespace Glorpen\Propel\PropelBundle\Services;

use Glorpen\Propel\PropelBundle\Events\DetectOMClassEvent;

use Glorpen\Propel\PropelBundle\Events\PeerEvent;

class OMClassOverrider {
	
	private $classes = array();
	
	public function __construct(array $map){
		$this->classes = $map;
	}
	
	public function getClassForOM($omClass){
		if(array_key_exists($omClass, $this->classes)){
			return $this->classes[$omClass];
		} else {
			return null;
		}
	}
	
	public function getExtendedClass($class){
		$ret = array_search($class, $this->classes);
		return $ret?$ret:null;
	}
	
	public function onDetectionRequest(DetectOMClassEvent $e){
		if(!$e->isDetected()){
			$e->setDetectedClass($this->getClassForOM($e->getClass()));
		}
	}
}
