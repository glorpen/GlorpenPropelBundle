<?php

/**
 * This file is part of the GlorpenPropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license GPLv3
 */

namespace Glorpen\Propel\PropelBundle\Tests;

use Glorpen\Propel\PropelBundle\Services\TransactionLifeCycle;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Glorpen\Propel\PropelBundle\Events\QueryEvent;

use Glorpen\Propel\PropelBundle\Events\PeerEvent;

use Glorpen\Propel\PropelBundle\Events\ConnectionEvent;

use Glorpen\Propel\PropelBundle\Connection\EventPropelPDO;

use Glorpen\Propel\PropelBundle\Services\ContainerAwareModel;

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

use Glorpen\Propel\PropelBundle\Dispatcher\EventDispatcherProxy;

use Glorpen\Propel\PropelBundle\Tests\PropelTestCase;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\Book;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\BookQuery;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\BookPeer;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\SoftdeleteTable;

use Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\om\BaseSoftdeleteTableQuery;

/**
 * @author Arkadiusz Dzięgiel
 */
class EventTriggeringTest extends PropelTestCase {
	
	public function setUp()
	{
		parent::setUp();
		$this->loadAndBuild();
	}
	
	private $oldConnection;
	
	public function testContainerSetting(){
		
		$that = $this;
		
		EventDispatcherProxy::setDispatcherGetter(function() use ($that){
			$c = $that->getContainer();
			$d = new ContainerAwareEventDispatcher($c);
			
			$d->addListener('model.construct', array(new ContainerAwareModel($c), 'onModelConstruct'));
			
			return $d;
		});
		
		$b = new Book();
		$this->assertTrue($b->hasContainer(), 'Container is set on object creation');
	}
	
	public function setUpEventHandlers(){
		$triggered = new \stdClass();
		$events = func_get_args();
		
		foreach($events as $e){
			if(is_string($e)) $triggered->{$e} = 0;
		}
		
		$that = $this;
		
		EventDispatcherProxy::setDispatcherGetter(function() use ($that, &$triggered, $events){
			$c = $that->getContainer();
			$d = new ContainerAwareEventDispatcher($c);
			
			foreach($events as $e){
				if($e instanceof EventSubscriberInterface){
					$d->addSubscriber($e);
				} else {
					$d->addListener($e, function() use($e, &$triggered){
						$triggered->{$e}++;
					});
				}
			}
		
			return $d;
		});
		
		return $triggered;
	}
	
	public function assertEventTriggered($msg, $ctx){
		$args = array_slice(func_get_args(), 2);
		$ctx = (array)$ctx;
		$k = array_combine(array_keys($ctx), $args);
		foreach($k as $key=>$val){
			$this->assertEquals($val, $ctx[$key], $msg.' for '.$key);
		}
	}
	
	public function testEventsTriggering(){
		
		//model
		
		$ctx = $this->setUpEventHandlers('construct','model.construct');
		$m = new Book();
		$this->assertEventTriggered('On new model construct', $ctx, 1,1);
		
		$ctx = $this->setUpEventHandlers('model.insert.post','model.insert.pre', 'model.save.pre', 'model.save.post');
		$m->save();
		$this->assertEventTriggered('On new model insert', $ctx, 1,1,1,1);
		
		$ctx = $this->setUpEventHandlers('model.update.post','model.update.pre', 'model.save.pre', 'model.save.post', 'update.pre', 'update.post');
		$m->setTitle('title');
		$m->save();
		$this->assertEventTriggered('On model update', $ctx, 1,1,1,1,1,1);
		
		$ctx = $this->setUpEventHandlers('model.delete.post','model.delete.pre', 'delete.pre', 'delete.post');
		$m->delete();
		$this->assertEventTriggered('On model delete', $ctx, 1,1,2,2); // 2,2 for pre/postDelete from Query object
		
		//query
		
		$ctx = $this->setUpEventHandlers('construct','query.construct');
		$q = new BookQuery();
		$this->assertEventTriggered('On new query construct', $ctx, 1,1);
		
		$ctx = $this->setUpEventHandlers('query.update.post','query.update.pre', 'update.pre', 'update.post');
		$q->update(array('Title'=>'the title'));
		$this->assertEventTriggered('On query update', $ctx, 1,1,1,1);
		
		$ctx = $this->setUpEventHandlers('query.delete.post','query.delete.pre', 'delete.pre', 'delete.post');
		$q->filterByTitle('test')->delete();
		$this->assertEventTriggered('On query delete', $ctx, 1,1,1,1);
		
		$ctx = $this->setUpEventHandlers('query.select.pre');
		$q->find();
		$this->assertEventTriggered('On query select', $ctx, 1);
		
		//connection
		
		$ctx = $this->setUpEventHandlers('connection.create');
		$con = new EventPropelPDO("sqlite::memory:");
		$this->assertEventTriggered('On connection create', $ctx, 1);
		
		$ctx = $this->setUpEventHandlers('connection.commit.pre','connection.commit.post');
		$con = \Propel::getConnection();
		$con->beginTransaction();
		$con->beginTransaction();
		
		$con->commit();
		$this->assertEventTriggered('On connection nested commt', $ctx, 0,0);
		$con->commit();
		$this->assertEventTriggered('On connection commt', $ctx, 1,1);
		
		$ctx = $this->setUpEventHandlers('connection.rollback.pre','connection.rollback.post');
		$con = \Propel::getConnection();
		$con->beginTransaction();
		$con->beginTransaction();
		
		$con->rollBack();
		$this->assertEventTriggered('On connection nested rollback', $ctx, 0,0);
		$con->rollBack();
		$this->assertEventTriggered('On connection rollback', $ctx, 1,1);
	}
	
	public function testEvents(){
		$con = \Propel::getConnection();
		$e = new ConnectionEvent($con);
		$this->assertSame($con, $e->getConnection());
		
		$e = new PeerEvent($cls='SomeClass');
		$this->assertEquals($cls, $e->getClass());
		
		$e = new QueryEvent($q=new BookQuery());
		$this->assertSame($q, $e->getQuery());
	}
	
	public function testTransaction(){
		$con = \Propel::getConnection();
		
		$service = new TransactionLifeCycle();
		
		$a = new Book();
		$faulty = new Book();
		$b = new Book();
		
		$ctx = $this->setUpEventHandlers($service);
		$faulty->setTitle(new \stdClass());
		
		$con->beginTransaction();
		
		try{
			$a->save();
			$faulty->save();
			$b->save();
			$this->fail("Transaction handling");
		} catch(\Exception $e){
			$con->rollBack();
		}
		
		$this->assertEquals(false, $a->commited || $a->rolledback, 'Commited and rolledback hooks are not run if DB transaction didn\'t commit');
		$this->assertClearCache($service);
		
		$faulty->setTitle("test");
		$faulty->enableTransactionError();
		
		$con->beginTransaction();
		try{
			$a->save();
			$faulty->save();
			$b->save();
			$con->commit();
			$this->fail("Transaction faulty commit handling");
		} catch(\Exception $e){
			$con->rollBack();
		}
		
		$this->assertEquals(true, $a->rolledback, 'Commited model was rolled back');
		$this->assertEquals(false, $b->rolledback || $faulty->rolledback, 'Uncommited models were not rolled back');
		$this->assertClearCache($service);
		
		$con->beginTransaction();
		try{
			$a->save();
			$b->save();
			$con->commit();
		} catch(\Exception $e){
			$this->fail("Successful commit");
		}
		
		$this->assertClearCache($service);
		
		$ctx = $this->setUpEventHandlers('connection.commit.pre', $service);
		$a->setTitle("test");
		$a->save();
		
		$this->assertEventTriggered('Transaction events on singular model', $ctx, 1);
		$this->assertClearCache($service, 'Single commit');
	}
	
	protected function assertClearCache(TransactionLifeCycle $service, $msg=null){
		//check if cache is clear
		
		$r = new \ReflectionObject($service);
		
		$refModels = $r->getProperty("models");
		$refModels->setAccessible(true);
		$this->assertCount(0, $refModels->getValue($service), 'Service cached models'.($msg?' - '.$msg:''));
		
		$refModels = $r->getProperty("processedModels");
		$refModels->setAccessible(true);
		$this->assertCount(0, $refModels->getValue($service), 'Service cached processed models'.($msg?' - '.$msg:''));
	}
	
	public function testPreEventWithSoftDeleteBehavior(){
		$that = $this;
		$order = array();
		
		EventDispatcherProxy::setDispatcherGetter(function() use ($that, &$order){
			$c = $that->getContainer();
			$d = new ContainerAwareEventDispatcher($c);
				
			$d->addListener('model.delete.pre', function($e) use ($that, &$order){
				$order[] = 'model.delete.pre';
			});
			$d->addListener('delete.pre', function($e) use ($that, &$order){
				$order[] = 'delete.pre';
			});
			$d->addListener('query.delete.pre', function($e) use ($that, &$order){
				$order[] = 'query.delete.pre';
			});
			
			return $d;
		});
		
		$m = new SoftdeleteTable();
		$m->save();
		$m->delete();
		
		$that->assertContains('model.delete.pre', $order, 'Delete model event');
		$that->assertContains('delete.pre', $order, 'Model global delete event');
		
		$order = array();
		
		BaseSoftdeleteTableQuery::create()->filterById(1)->delete();
		$that->assertContains('delete.pre', $order, 'Query global delete event');
		$that->assertContains('query.delete.pre', $order, 'Query delete event');
	}
	
}
