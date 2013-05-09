<?php
namespace Glorpen\Propel\PropelBundle\Services;

/**
 * @author Arkadiusz Dzięgiel
 */
class PropelClassFinder {
	
	public function findQueryClass($modelClass){
		//the propel way, like in \PropelQuery
		$queryClass = $modelClass . 'Query';
		if (class_exists($queryClass)) return $queryClass;
		
		//try with peer
		if(class_exists($modelClass) && defined($modelClass.'::PEER')){
			$peerClass = $modelClass::PEER;
			if(!$peerClass::getTableMap()->isSingleTableInheritance()){
				$queryClass = substr($peerClass,0,-4).'Query';
				if (class_exists($queryClass)) return $queryClass;
			} else {
				//single inheritance
				$peerRef = new \ReflectionClass($peerClass);
				$modelRef = new \ReflectionClass($modelClass);
				
				foreach($peerRef->getConstants() as $name=>$value){
					if(strncmp('CLASSNAME_', $name, 10) == 0 && $modelRef->isSubclassOf($value)){
						$queryClass = $value.'Query';
						if (class_exists($queryClass)) return $queryClass;
						break;
					}
				}
			}
		}
		
		throw new \LogicException('Can\'t find query class for '.$modelClass);
	}
	
	public function findPeerClass($modelClass){
		return $modelClass::PEER;
	}
	
	public function findModelClass($peerClass){
		return $peerClass::getOMClass();
	}
	
}
