<?php
namespace Glorpen\Propel\PropelBundle\Tests\Fixtures\Model;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\om\BasePerson;

class RectanglePerson extends BasePerson
{
    public function __construct()
    {
        parent::__construct();
        $this->setClassKey(PersonPeer::CLASSKEY_3);
    }
}
