<?php

namespace Glorpen\Propel\PropelBundle\Provider;

interface OMClassProvider {

	/**
	 * Returns extending class
	 * @param unknown $row
	 * @param unknown $col
	 */
	public function getOMClass($row, $col);
	/**
	 * Returns class mapping handles by this provider, eg. array(MyUserClass => FosUser)
	 */
	public function getMapping();

}
