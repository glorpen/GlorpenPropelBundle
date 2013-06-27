<?php

namespace Glorpen\Propel\PropelBundle\Services;

use Glorpen\Propel\PropelBundle\Connection\EventPropelPDO;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Glorpen\Propel\PropelBundle\Events\ModelEvent;

use Glorpen\Propel\PropelBundle\Events\ConnectionEvent;

/**
 * TODO: multiple connections
 * @author Arkadiusz Dzięgiel
 */
class TransactionLifeCycle implements EventSubscriberInterface {
	
	static protected $events = array('save','update','insert','delete');
	
	protected $models = array(), $processedModels=array();
	
	public function onModelEvent($eventType, ModelEvent $event){
		$model = $event->getModel();
		$peer = $model::PEER;
		$con = \Propel::getConnection($peer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
		
		if($con instanceof EventPropelPDO && $con->inTransaction()){
			$this->models[] = array($event->getModel(), $eventType);
		}
	}
	
	public function __call($m, $args){
		if(strncmp($m, 'onModel', 7)==0){
			$this->onModelEvent(strtolower(substr($m,7)), $args[0]);
		}
	}
	
	protected function applyTransactionState($isCommit, $models, \PropelPDO $con = null){
		$type=$isCommit?'commit':'rollback';
		foreach($models as $m){
			list($model, $eventType) = $m;
			$model->{'pre'.ucfirst($type).ucfirst($eventType)}($con);
			$model->{'pre'.ucfirst($type)}($con);
			if($isCommit) $this->processedModels[] = $m;
		}
		if($isCommit) $this->processedModels = array();
	}
	
	public function onCommit(ConnectionEvent $event){
		$models = $this->models;
		$this->models = array();
		$this->applyTransactionState(true, $models, $event->getConnection());
	}
	
	public function onRollback(ConnectionEvent $event){
		$models = $this->processedModels;
		$this->processedModels = array();
		$this->applyTransactionState(false, $models, $event->getConnection());
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
