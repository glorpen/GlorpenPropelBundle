<?php
class EventBehavior extends Behavior {
	
	protected $parameters = array();
	
	public function postInsert(){
		return <<<EOF
EventDispatcherProxy::trigger('model.insert.post', new ModelEvent(\$this));
EOF;
	}
	public function postUpdate(){
		return <<<EOF
EventDispatcherProxy::trigger(array('update.post', 'model.update.post'), new ModelEvent(\$this));
EOF;
	}
	public function postDelete(){
		return <<<EOF
EventDispatcherProxy::trigger(array('delete.post', 'model.delete.post'), new ModelEvent(\$this));
EOF;
	}
	public function postSave(){
		return <<<EOF
EventDispatcherProxy::trigger('model.save.post', new ModelEvent(\$this));
EOF;
	}
	
	public function preInsert(){
		return <<<EOF
EventDispatcherProxy::trigger('model.insert.pre', new ModelEvent(\$this));
EOF;
	}
	public function preUpdate(){
		return <<<EOF
EventDispatcherProxy::trigger(array('update.pre', 'model.update.pre'), new ModelEvent(\$this));
EOF;
	}
	public function preDelete(){
		return <<<EOF
EventDispatcherProxy::trigger(array('delete.pre','model.delete.pre'), new ModelEvent(\$this));
EOF;
	}
	public function preSave(){
		return <<<EOF
EventDispatcherProxy::trigger('model.save.pre', new ModelEvent(\$this));
EOF;
	}
	
	public function preDeleteQuery(){
		return <<<EOF
EventDispatcherProxy::trigger(array('delete.pre','query.delete.pre'), new QueryEvent(\$this));
EOF;
	}
	
	public function postDeleteQuery(){
		return <<<EOF
EventDispatcherProxy::trigger(array('delete.post','query.delete.post'), new QueryEvent(\$this));
EOF;
	}

	public function preSelectQuery(){
		return <<<EOF
EventDispatcherProxy::trigger('query.select.pre', new QueryEvent(\$this));
EOF;
	}
	
	public function postSelectQuery(){
		return <<<EOF
EventDispatcherProxy::trigger('query.select.post', new QueryEvent(\$this));
EOF;
	}

	public function preUpdateQuery(){
		return <<<EOF
EventDispatcherProxy::trigger(array('update.pre', 'query.update.pre'), new QueryEvent(\$this));
EOF;
	}

	public function postUpdateQuery(){
		return <<<EOF
EventDispatcherProxy::trigger(array('update.post', 'query.update.post'), new QueryEvent(\$this));
EOF;
	}
	
	
	public function objectFilter(&$script){
		$rep = <<<EOF
	EventDispatcherProxy::trigger(array('construct','model.construct'), new ModelEvent(\$this));
	
EOF;
		$script = preg_replace('/(parent::__construct[^}]*)/', '\1'.$rep, $script);
	}
	
	public function queryFilter(&$script)
	{
		$rep = <<<EOF
	EventDispatcherProxy::trigger(array('construct','query.construct'), new QueryEvent(\$this));
	
EOF;
		$script = preg_replace('/(parent::__construct[^}]*)/', '\1'.$rep, $script);
	}
	
	public function peerFilter(&$script){
		$peerClass = $this->getTable()->getNamespace().'\\om\\Base'.$this->getTable()->getPhpName().'Peer';
		
		$script .= <<<EOF
EventDispatcherProxy::trigger(array('construct','peer.construct'), new PeerEvent('{$peerClass}'));

EOF;
	}
	
	public function objectMethods($builder)
	{
		$builder->declareClass('Glorpen\\PropelEvent\\PropelEventBundle\\Events\\ModelEvent');
		$builder->declareClass('Glorpen\\PropelEvent\\PropelEventBundle\\Dispatcher\\EventDispatcherProxy');
	}
	
	public function queryMethods($builder)
	{
		$builder->declareClass('Glorpen\\PropelEvent\\PropelEventBundle\\Events\\QueryEvent');
		$builder->declareClass('Glorpen\\PropelEvent\\PropelEventBundle\\Dispatcher\\EventDispatcherProxy');
	}
	
	public function staticMethods($builder){
		$builder->declareClass('Glorpen\\PropelEvent\\PropelEventBundle\\Events\\PeerEvent');
		$builder->declareClass('Glorpen\\PropelEvent\\PropelEventBundle\\Dispatcher\\EventDispatcherProxy');
	}
	
}
