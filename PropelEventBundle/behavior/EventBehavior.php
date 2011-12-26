<?php
class EventBehavior extends Behavior {
	
	protected $parameters = array();
	
	public function postInsert(){
		return <<<EOF
EventDispatcherProxy::trigger('insert.post', new ModelEvent(\$this));
EOF;
	}
	public function postUpdate(){
		return <<<EOF
EventDispatcherProxy::trigger('update.post', new ModelEvent(\$this));
EOF;
	}
	public function postDelete(){
		return <<<EOF
EventDispatcherProxy::trigger('delete.post', new ModelEvent(\$this));
EOF;
	}
	public function postSave(){
		return <<<EOF
EventDispatcherProxy::trigger('save.post', new ModelEvent(\$this));
EOF;
	}
	
	
	public function preInsert(){
		return <<<EOF
EventDispatcherProxy::trigger('insert.pre', new ModelEvent(\$this));
EOF;
	}
	public function preUpdate(){
		return <<<EOF
EventDispatcherProxy::trigger('update.pre', new ModelEvent(\$this));
EOF;
	}
	public function preDelete(){
		return <<<EOF
EventDispatcherProxy::trigger('delete.pre', new ModelEvent(\$this));
EOF;
	}
	public function preSave(){
		return <<<EOF
EventDispatcherProxy::trigger('save.pre', new ModelEvent(\$this));
EOF;
	}
	
	public function preDeleteQuery(){
		return <<<EOF
EventDispatcherProxy::trigger('delete.pre', new QueryEvent(\$this));
EOF;
	}
	
	public function postDeleteQuery(){
		return <<<EOF
EventDispatcherProxy::trigger('delete.post', new QueryEvent(\$this));
EOF;
	}

	public function preSelectQuery(){
		return <<<EOF
EventDispatcherProxy::trigger('select.pre', new QueryEvent(\$this));
EOF;
	}
	
	public function postSelectQuery(){
		return <<<EOF
EventDispatcherProxy::trigger('select.post', new QueryEvent(\$this));
EOF;
	}
	
	public function objectFilter(&$script){
		$rep = <<<EOF
	EventDispatcherProxy::trigger('construct.after', new ModelEvent(\$this));
	
EOF;
		$script = preg_replace('/(parent::__construct[^}]*)/', '\1'.$rep, $script);
	}
	
	public function queryFilter(&$script)
	{
		$rep = <<<EOF
	EventDispatcherProxy::trigger('construct.after', new QueryEvent(\$this));
	
EOF;
		$script = preg_replace('/(parent::__construct[^}]*)/', '\1'.$rep, $script);
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
}
