<?php

/**
 * This file is part of the GlorpenPropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license GPLv3
 */

namespace Glorpen\Propel\PropelBundle\Tests;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Arkadiusz Dzięgiel
 */
class SymfonyServicesTest extends WebTestCase
{
    
    public function setUp()
    {
        
    }

    public function testSomething()
    {
        $kernel = $this->createKernel();
        $kernel->boot();
        $c = $kernel->getContainer();
        
        $c->get('glorpen.propel.event.dispatcher');
    }
    
    protected static function getKernelClass()
    {
        return 'Glorpen\Propel\PropelBundle\Tests\TestKernel';
    }
}
