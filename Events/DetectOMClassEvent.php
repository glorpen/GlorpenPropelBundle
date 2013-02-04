<?php

namespace Glorpen\Propel\PropelBundle\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Arkadiusz Dzięgiel
 */
class DetectOMClassEvent extends Event {
	private $cls, $detectedClass;
	
	public function __construct($cls){
		$this->cls = $cls;
	}
	
	public function getClass(){
		return $this->cls;
	}
	
	public function setDetectedClass($cls){
		$this->detectedClass = $cls;
	}
	
	public function isDetected(){
		return $this->detectedClass !== null;
	}
	
	public function getDetectedClass(){
		return $this->detectedClass;
	}
}
