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
		
		//until https://github.com/propelorm/Propel/pull/592 is not merged into propel
		$script = preg_replace('/(public static function populateObject.*?\$cls = [^:]+::)OM_CLASS/s', '\1getOMClass(\$row, \$startcol)', $script);
		
	}
	
	public function queryFilter(&$script){
		//until https://github.com/propelorm/Propel/pull/592 is not merged into propel
		$script = preg_replace('/(protected function findPkSimple\(\$key, \$con\).*?)\$obj = new ([^$][^(]+)[\(\)]{2}/s','$1\$cls = $2Peer::getOMClass();'."\n\t\t\t".'\$obj = new \$cls', $script);
		//return correct isntance in query::create
		$script = preg_replace('/(static function create\(.*?\$query = new )([^\(]+)/s','$1static', $script);
	}
	
	public function staticMethods($builder){
		$builder->declareClass('Glorpen\\Propel\\PropelBundle\\Events\DetectOMClassEvent');
		$builder->declareClass('Glorpen\\Propel\\PropelBundle\\Dispatcher\\EventDispatcherProxy');
	}
	
	public function queryMethods($builder){
		
		//fix on-demand-formatter
		return <<<EOF
public function setFormatter(\$formatter)
{
	if (is_string(\$formatter) && \$formatter === \\ModelCriteria::FORMAT_ON_DEMAND) {
		\$formatter = '\Glorpen\Propel\PropelBundle\Formatter\PropelOnDemandFormatter';
	}
			
	return parent::setFormatter(\$formatter);
}
EOF;
	}
	
}
