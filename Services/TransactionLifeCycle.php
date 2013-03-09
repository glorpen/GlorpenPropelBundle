<?php

namespace Glorpen\Propel\PropelBundle\Services;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Glorpen\Propel\PropelBundle\Events\ModelEvent;

use Glorpen\Propel\PropelBundle\Events\ConnectionEvent;

/**
 * @author Arkadiusz Dzięgiel
 */
class TransactionLifeCycle implements EventSubscriberInterface {
	
	static protected $events = array('save','update','insert','delete');
	
	protected $models = array(), $processedModels=array();
	
	public function onModelEvent($eventType, ModelEvent $event){
		$this->models[] = array($event->getModel(), $eventType);
	}
	
	public function __call($m, $args){
		if(strncmp($m, 'onModel', 7)==0){
			$this->onModelEvent(strtolower(substr($m,7)), $args[0]);
		}
	}
	
	protected function applyTransactionState($type, $models, \PropelPDO $con = null){
		foreach($models as $m){
			list($model, $eventType) = $m;
			$model->{'pre'.ucfirst($type).ucfirst($eventType)}($con);
			$model->{'pre'.ucfirst($type)}($con);
			if($type=='commit') $this->processedModels[] = $m;
		}
	}
	
	public function onCommit(ConnectionEvent $event){
		$models = $this->models;
		$this->models = array();
		$this->applyTransactionState('commit', $models, $event->getConnection());
	}
	
	public function onRollback(ConnectionEvent $event){
		$models = $this->processedModels;
		$this->processedModels = array();
		$this->applyTransactionState('rollback', $models, $event->getConnection());
	}
	
	public static function getSubscribedEvents(){
		$ret=array(
			'connection.commit.pre' => 'onCommit',
			'connection.rollback.pre' => 'onRollback',
		);
		foreach(self::$events as $e){
			$ret['model.'.$e.'.post'] = 'onModel'.ucfirst($e);
		}
		return $ret;
	}
}
