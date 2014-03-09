<?php

namespace Glorpen\Propel\PropelBundle\Provider;

interface OMClassProvider {

	public function getOMClass($row, $col);
	public function getSubscribedClasses();

}
