<?php

namespace Glorpen\Propel\PropelBundle\Tests\Fixtures\Services;

use Glorpen\Propel\PropelBundle\Provider\OMClassProvider;

class TestOMProvider implements OMClassProvider {

	protected $from, $to;
	
	public function __construct($from, \Closure $to){
		$this->from = $from;
		$this->to = $to;
	}
	
	public function getSubscribedClasses() {
		return $this->from;
	}

	public function getOMClass($row, $col) {
		return call_user_func($this->to, $row, $col);
	}

}
