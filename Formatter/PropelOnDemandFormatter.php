<?php
namespace Glorpen\Propel\PropelBundle\Formatter;

use \PropelOnDemandFormatter as BaseFormatter;

class PropelOnDemandFormatter extends BaseFormatter
{
    
    public function init(\ModelCriteria $criteria)
    {
        parent::init($criteria);
        $this->isSingleTableInheritance = true; //so peer::getOMClass will be called
        return $this;
    }
}
