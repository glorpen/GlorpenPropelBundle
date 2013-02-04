<?php
class ExtendBehavior extends Behavior {
	
	protected $parameters = array();
	
	public function peerFilter(&$script){
		$peerClass = $this->getTable()->getNamespace().'\\om\\Base'.$this->getTable()->getPhpName().'Peer';
		
		$getOMClass = <<<EOF
\\1
		\$event = new DetectOMClassEvent(\\2);
		EventDispatcherProxy::trigger('om.detect', \$event);
		if(\$event->isDetected()){
			return \$event->getDetectedClass();
		}
		
		return \\2;
EOF;
		$script = preg_replace(
			'/(public static function getOMClass\([^{]+{.*?)return ([^;]+);/s',
			$getOMClass,
			$script
		);
		
	}
	
	public function staticMethods($builder){
		$builder->declareClass('Glorpen\\Propel\\PropelBundle\\Events\DetectOMClassEvent');
	}
	
}
