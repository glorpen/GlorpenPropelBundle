<?php
namespace Glorpen\Propel\PropelBundle\Provider;

/**
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
interface OMClassProvider
{

    /**
     * Returns extending class
     *
     * @param unknown $row
     * @param unknown $col
     */
    public function getOMClass($row, $col);

    /**
     * Returns class mapping handles by this provider, eg.
     * array(MyUserClass => FosUser)
     */
    public function getMapping();
}
