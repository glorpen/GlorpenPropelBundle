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
	
	protected $models = array();
	
	public function onModelEvent($eventType, ModelEvent $event){
		$this->models[] = array($event->getModel(), $eventType);
	}
	
	public function __call($m, $args){
		if(strncmp($m, 'onModel', 7)==0){
			$this->onModelEvent(strtolower(substr($m,7)), $args[0]);
		}
	}
	
	protected function applyTransactionState($type, \PropelPDO $con = null){
		var_dump($this->models);
		foreach($this->getModels() as $m){
			list($model, $eventType) = $m;
			$model->{'pre'.ucfirst($type).ucfirst($eventType)}($con);
			$model->{'pre'.ucfirst($type)}($con);
		}
	}
	
	public function onCommit(ConnectionEvent $event){
		$this->applyTransactionState('commit', $event->getConnection());
	}
	
	/*
	public function onRollback(ConnectionEvent $event){
		$this->applyTransactionState('rollback');
	}
	*/
	
	protected function getModels(){
		$models = $this->models;
		$this->models = array();
		return $models;
	}
	
	public static function getSubscribedEvents(){
		$ret=array(
			'connection.commit' => 'onCommit',
			//'connection.rollback' => 'onRollback',
		);
		foreach(self::$events as $e){
			$ret['model.'.$e.'.post'] = 'onModel'.ucfirst($e);
		}
		return $ret;
	}
}
