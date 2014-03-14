<?php

/**
 * This file is part of the GlorpenPropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license GPLv3
 */

namespace Glorpen\Propel\PropelBundle\Tests;

use Glorpen\Propel\PropelBundle\Services\PropelClassFinder;

use Glorpen\Propel\PropelBundle\Services\OMClassOverrider;

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

use Glorpen\Propel\PropelBundle\Dispatcher\EventDispatcherProxy;

use Glorpen\Propel\PropelBundle\Tests\PropelTestCase;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\Book;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\BookQuery;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\BookPeer;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\SiThingQuery;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\SiThing;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Services\TestOMProvider;

/**
 * @author Arkadiusz Dzięgiel
 */
class PropelExtendingTest extends PropelTestCase {
	
	protected function setUp(){
		self::$map = array(
			self::$modelClass => self::$extendedClass,
			'Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\TrianglePerson' => 'Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\ExtendedTrianglePerson',
			'Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\SiThing' => 'Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\ExtendedSiThing'
		);
		
		$this->loadAndBuild();
	}
	
	static protected $extendedClass = 'Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\ExtendedBook';
	static protected $modelClass = 'Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\Book';
	
	static public $map;
	
	protected function setUpListener(){
		$that = $this;
		$overrider = new OMClassOverrider(PropelExtendingTest::$map);
		EventDispatcherProxy::setDispatcherGetter(function() use ($that, $overrider){
			$c = $that->getContainer();
			$d = new ContainerAwareEventDispatcher($c);
			
			$d->addListener('om.detect', array($overrider, 'onDetectionRequest'));
				
			return $d;
		});
		
		return $overrider;
	}
	
	public function testExtending(){
		
		$this->setUpListener();
		
		$this->assertEquals(self::$extendedClass, BookPeer::getOMClass(), 'normal call to getOMClass');
		
		\Propel::disableInstancePooling();
		
		$b = new Book();
		$b->setTitle("extended-title");
		$b->save();
		
		$b = BookQuery::create()->findOne();
		$this->assertInstanceOf(static::$extendedClass, $b, 'findOne returns right type');
		$b = BookQuery::create()->findPk(1);
		$this->assertInstanceOf(static::$extendedClass, $b, 'findPk returns right type');
		
		$bs = BookQuery::create()->setFormatter(BookQuery::FORMAT_ON_DEMAND)->find();
		foreach($bs as $b){
			$this->assertInstanceOf(static::$extendedClass, $b, 'custom onDemandFormatter is used');
		}
		
		$b = BookPeer::retrieveByPk(1);
		$this->assertInstanceOf(static::$extendedClass, $b, 'retrieveByPk returns right type');
	}
	
	public function testService(){
		
		$org = 'OriginalClass';
		$ext = 'ExtendedClass';
		
		$s = new OMClassOverrider(array($org=>$ext));
		
		$this->assertEquals($ext, $s->getClassForOM($org));
		$this->assertSame(null, $s->getClassForOM('not existent'));
		
		$clsObject = (object) array('cls'=>$ext);
		$p = new TestOMProvider(function() use ($clsObject) {
			return $clsObject->cls;
		}, array(
			'SomeA' => $org,
			'SomeB' => $org
		));
		$s->addProvider($p);
		
		$this->assertEquals($ext, $s->getClassForOM($org));
		$this->assertSame(null, $s->getClassForOM('not existent'));
		
		$this->assertEquals($org, $s->getExtendedClass('SomeA'));
		$this->assertEquals($org, $s->getExtendedClass('SomeB'));
		
		$clsObject->cls = 'SomeClass';
		$this->assertEquals($clsObject->cls, $s->getClassForOM($org), 'return class from service');
		
		$clsObject->cls = null;
		$this->assertEquals($ext, $s->getClassForOM($org), 'revert to map if no response from service');
	}

	public function testClassFinder(){
		
		$this->setUpListener();
		
		$finder = new PropelClassFinder();
		
		$normalQuery = static::$modelClass.'Query';
		$normalPeer = static::$modelClass.'Peer';
		
		$this->assertEquals($normalQuery, $finder->findQueryClass(static::$extendedClass));
		$this->assertEquals($normalPeer, $finder->findPeerClass(static::$extendedClass));
		$this->assertEquals(static::$extendedClass, $finder->findModelClass($normalPeer));
		
		$this->assertEquals($normalQuery, $finder->findQueryClass(static::$modelClass));
		$this->assertEquals($normalPeer, $finder->findPeerClass(static::$modelClass));
		
		$personModel = 'Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\Person';
		$personQuery = $personModel.'Query';
		$this->assertEquals($personQuery, $finder->findQueryClass($personModel));
		
		$this->assertEquals($personModel, $finder->findModelClass('Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\ExtendedPeoplePeer'));
		$this->assertEquals('Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\TrianglePersonQuery', $finder->findQueryClass('Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\ExtendedTrianglePerson'), 'Extending when using single ineritance');
		
		try{
			$finder->findQueryClass('not existent');
			$this->fail('Exception on non existent class');
		}catch(\LogicException $e){
		}
	}
	
	//https://github.com/glorpen/GlorpenPropelBundle/issues/2
	public function testSingleInheritance(){
		
		$this->setUpListener();
		\Propel::disableInstancePooling();
		
		$p = new SiThing();
		$p->save();
		
		$this->assertInstanceOf('Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\ExtendedSiThing', SiThingQuery::create()->findPk($p->getPrimaryKey()));
	}
	
	//inspiration: https://bitbucket.org/glorpen/glorpenpropelbundle/pull-request/2/change-xpeer-getomclass-to-self-getomclass/diff
	public function testQueryModelClassOverride(){
		
		$manualFirst = 'Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\ManualBook';
		$manualSecond = 'Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\ManualSecondBook';
		
		\Propel::disableInstancePooling();
		
		$o = $this->setUpListener();
		$p = new TestOMProvider(
			function($row, $cols) use($manualFirst, $manualSecond){
				if($row[0]==1){
					return $manualFirst;
				} else {
					return $manualSecond;
				}	
			},
			array(
				$manualFirst=>self::$modelClass,
				$manualSecond=>self::$modelClass
			)
		);
		$o->addProvider($p);
		
		$b = new Book();
		$b->setTitle("extended-title");
		$b->save();
		
		$b = new Book();
		$b->setTitle("extended-title2");
		$b->save();
		
		$manual1 = BookQuery::create()->filterById(1)->findOne();
		$this->assertInstanceOf($manualFirst, $manual1);
		
		$manual2 = BookQuery::create()->filterById(2)->findOne();
		$this->assertInstanceOf($manualSecond, $manual2);
	}
}
