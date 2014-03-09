<?php

namespace Glorpen\Propel\PropelBundle\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Arkadiusz Dzięgiel
 */
class DetectOMClassEvent extends Event {
	private $cls, $detectedClass, $row, $col;
	
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
	
	public function setRow($row){
		$this->row = $row;
	}
	
	public function getRow(){
		return $this->row;
	}
	
	public function setCol($col){
		$this->col = $col;
	}
	
	public function getCol(){
		return $this->col;
	}
}
