<?php
class EventBehavior extends Behavior {
	
	protected $tableModificationOrder = 51;
	
	protected $parameters = array();
	
	public function postInsert(){
	    return $this->getModelHook(array('model.insert.post'));
	}
	public function postUpdate(){
	    return $this->getModelHook(array('update.post', 'model.update.post'));
	}
	public function postDelete(){
	    return $this->getModelHook(array('delete.post', 'model.delete.post'));
	}
	public function postSave(){
	    return $this->getModelHook(array('model.save.post'));
	}
	
	public function preInsert(){
	    return $this->getModelHook(array('model.insert.pre'));
	}
	public function preUpdate(){
	    return $this->getModelHook(array('update.pre', 'model.update.pre'));
	}
	public function preSave(){
	    return $this->getModelHook(array('model.save.pre'));
	}
	
	public function preDeleteQuery(){
		return <<<EOF
// placeholder, issue #5
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
	
	protected function getModelHook(array $events){
	    $sEvents = 'array(\''.implode('\',\'', $events).'\')';
	    return 'EventDispatcherProxy::trigger('.$sEvents.', new ModelEvent($this));';
	}
	
	public function objectFilter(&$script){
		$rep = <<<EOF
	EventDispatcherProxy::trigger(array('construct','model.construct'), new ModelEvent(\$this));
	
EOF;
		$script = preg_replace('/(parent::__construct[^}]*)/', '\1'.$rep, $script, 1, $count);
		if($count == 0){
			$construct = <<<EOF
public function __construct(){
		parent::__construct();
	{$rep}}

	
EOF;
			$script = preg_replace('#(public function .*?}.*?)(/\*\*)#s', '\1'.$construct.'\2', $script, 1);
		}
		
		// fix for SoftDelete - handle preDelete as script filter
		$script = preg_replace('/(([\t ]+)public function delete.*?try[^{]{)/s', '\1'."\n".'\2\2\2EventDispatcherProxy::trigger(array(\'delete.pre\',\'model.delete.pre\'), new ModelEvent(\$this));', $script, 1);
		
		// add hooks after successful commit
		$script = preg_replace_callback('/([\t ]+)public function save.*?.*?} catch \(Exception \$e\)/s', array($this, 'modelFilterCallback'), $script);
		
		//$script = preg_replace(
	}
		
	public function modelFilterCallback($matches){
	    $text = $matches[0];
	    
	    $ret='return $affectedRows;';
	    
	    // "if" for proper handling of \sfMixer and maybe other behaviors
	    
	    $afterHooks = <<<EOF

if(\$affectedRows>0 && \$ret){
    if(\$isInsert) {
       {$this->getModelHook(array('model.insert.after'))}
    } else {
       {$this->getModelHook(array('model.update.after'))}
    }
    
    {$this->getModelHook(array('model.save.after'))}
}

$ret
EOF;

        preg_match("/^( +)return .affectedRows/m", $text, $matches);
        $afterHooks = preg_replace('/^/m', $matches[1], $afterHooks);
        return str_replace($ret, $afterHooks, $text);
	}
	
	public function queryFilter(&$script)
	{
		$rep = <<<EOF
	EventDispatcherProxy::trigger(array('construct','query.construct'), new QueryEvent(\$this));
	
EOF;
		$script = preg_replace('/(parent::__construct[^}]*)/', '\1'.$rep, $script);
		
		// fix for SoftDelete - handle preDelete as script filter
		$script = preg_replace('/(([\t ]+)protected function basePreDelete.*?{)/s', '\1'."\n".'\2\2EventDispatcherProxy::trigger(array(\'delete.pre\',\'query.delete.pre\'), new QueryEvent(\$this));', $script, 1);
	}
	
	public function peerFilter(&$script){
		$peerClass = $this->getTable()->getNamespace().'\\om\\Base'.$this->getTable()->getPhpName().'Peer';
		
		$script .= <<<EOF
EventDispatcherProxy::trigger(array('construct','peer.construct'), new PeerEvent('{$peerClass}'));

EOF;
	}
	
	public function objectMethods($builder)
	{
		$builder->declareClass('Glorpen\\Propel\\PropelBundle\\Events\\ModelEvent');
		$builder->declareClass('Glorpen\\Propel\\PropelBundle\\Dispatcher\\EventDispatcherProxy');
		
		$events=array('save','delete','update','insert');
		$types=array('commit','rollback');
		
		$ret='';
		
		foreach($types as $t){
			$ut = ucfirst($t);
			$ret.="public function pre{$ut}(\\PropelPDO \$con = null){}\n";
			foreach($events as $e){
				$ue = ucfirst($e);
				$ret.="public function pre{$ut}{$ue}(\\PropelPDO \$con = null){}\n";
			}
		}
		
		return $ret;
	}
	
	public function queryMethods($builder)
	{
		$builder->declareClass('Glorpen\\Propel\\PropelBundle\\Events\\QueryEvent');
		$builder->declareClass('Glorpen\\Propel\\PropelBundle\\Dispatcher\\EventDispatcherProxy');
	}
	
	public function staticMethods($builder)
	{
		$builder->declareClass('Glorpen\\Propel\\PropelBundle\\Events\\PeerEvent');
		$builder->declareClass('Glorpen\\Propel\\PropelBundle\\Dispatcher\\EventDispatcherProxy');
	}
	
}
