<?php
namespace Glorpen\Propel\PropelBundle\Tests;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Propel\PropelBundle\Tests\TestCase;

class PropelTestCase extends TestCase {
	
	static protected $root = __DIR__;
	
	static protected $schema = <<<SCHEMA
<database name="books" defaultIdMethod="native" namespace="Glorpen\\Propel\\PropelBundle\\Tests\\Fixtures\\Model">
    <table name="book">
        <column name="id" type="integer" required="true" primaryKey="true" autoIncrement="true" />
        <column name="title" type="varchar" size="255" primaryString="true" />
		<behavior name="event" />
		<behavior name="extend" />
    </table>
	<table name="person">
        <column name="id" type="integer" required="true" primaryKey="true" autoIncrement="true" />
        <column name="name" type="varchar" size="255" primaryString="true" />
			
		<column name="class_key" type="INTEGER" inheritance="single">
			<inheritance key="1" class="LongPerson"/>
			<inheritance key="2" class="TrianglePerson"/>
			<inheritance key="3" class="RectanglePerson"/>
		</column>
			
		<behavior name="event" />
		<behavior name="extend" />
    </table>
</database>
SCHEMA;
	
	protected function setUp()
	{
		if (!file_exists($file = static::$root . '/../vendor/propel/propel1/runtime/lib/Propel.php')) {
			$this->markTestSkipped('Propel is not available.');
		}
	
		require_once $file;
	}
	
	
	public function getContainer()
	{
		return new ContainerBuilder(new ParameterBag(array(
				'kernel.debug'      => false,
				'kernel.root_dir'   => static::$root . '/../',
		)));
	}
	
	protected function loadPropelQuickBuilder()
	{
		require_once static::$root . '/../vendor/propel/propel1/runtime/lib/Propel.php';
		require_once static::$root . '/../vendor/propel/propel1/runtime/lib/adapter/DBAdapter.php';
		require_once static::$root . '/../vendor/propel/propel1/runtime/lib/adapter/DBSQLite.php';
		require_once static::$root . '/../vendor/propel/propel1/runtime/lib/connection/PropelPDO.php';
		require_once static::$root . '/../vendor/propel/propel1/generator/lib/util/PropelQuickBuilder.php';
	}
	
	protected function loadAndBuild(){
		$this->loadPropelQuickBuilder();
	
		if(!class_exists('Glorpen\Propel\PropelBundle\Tests\Fixtures\Model\Book', false)){
			$builder = new \PropelQuickBuilder();
				
			$builder->getConfig()->setBuildProperty('behaviorEventClass', 'Behaviors.EventBehavior');
			$builder->getConfig()->setBuildProperty('behaviorExtendClass', 'Behaviors.ExtendBehavior');
				
			$builder->setSchema(static::$schema);
			$builder->setClassTargets(array('tablemap', 'peer', 'object', 'query', 'peerstub', 'querystub'));
			$builder->build();
		}
	}
}
