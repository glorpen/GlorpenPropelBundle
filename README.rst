-----------------
PropelEventBundle
-----------------

Propel events in Symfony2.

Using with Symfony2 2.1
-----------------------

To use this bundle with version 2.1 you have to override parameter dispatcher.class with:

::

    <parameter key="glorpen.propel.event.dispatcher.class">Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher</parameter>
    


How to install
--------------

- add this plugin to your project. From your project root:

    hg clone https://bitbucket.org/glorpen/glorpenpropeleventbundle Glorpen/PropelEvent

- enable the plugin in your **AppKernel** class

*app/AppKernel.php*

::

    <?php
    
    class AppKernel extends AppKernel
    {
       public function registerBundles()
       {
           $bundles = array(
               ...
               new Glorpen\PropelEvent\PropelEventBundle\PropelEventBundle(),
               ...
           );
       }
    }

- add behavior to propel config (only if you want use events on propel post/pre hooks)

or you can import PropelEventBundle/Resources/config/propel_config.yml for class definition

::

     propel:
        build_properties:
          propel.behavior.event.class: 'src.Glorpen.PropelEvent.PropelEventBundle.behavior.EventBehavior'
          propel.behavior.default: "event"


Listening for propel hooks
--------------------------

- register listener

::

	<service class="SomeBundle\Listeners\HistoryBehaviorListener">
		<argument type="service" id="security.context" />
		<tag name="propel.event" />
	</service>
	
	<service id="my.listener" class="SomeBundle\Listeners\HistoryBehaviorListener">
		<tag name="propel.event" method="onPropelEventSave" event="model.save.post" />
	</service>


Custom events
-------------

You can trigger events with generic or custom Event class, in following example **ValidationEvent**. 

- create **ValidationEvent** event

::
   
   <?php
   
   namespace YourBundle\Events;
   use Symfony\Component\Validator\Mapping\ClassMetadata;
   use Symfony\Component\EventDispatcher\Event;
   
   class ValidationEvent extends Event {
      private $metadata;
      
      public function __construct(ClassMetadata $metadata){
         $this->metadata = $metadata;
      }
      
      /**
       * @return \Symfony\Component\Validator\Mapping\ClassMetadata
       */
      public function getMetadata(){
         return $this->metadata;
      }
   }

- register listener in **services.xml**

::

   <service id="your.service" class="%your.service.class%">
      <argument>%your.service.argument%</argument>
      <tag name="propel.event" method="onProductLoadValidatorMetadata" event="product.validation" />
   </service>

- and then use it within model class

::

   <?php
   
   namespace YourBundle\Model;
   use YourBundle\Events\ValidationEvent;
   use Glorpen\PropelEvent\PropelEventBundle\Dispatcher\EventDispatcherProxy;
   use Symfony\Component\Validator\Mapping\ClassMetadata;
   use YourBundle\Model\om\BaseProduct;
   
   class Product extends BaseProduct {
      public static function loadValidatorMetadata(ClassMetadata $metadata)
      {
         EventDispatcherProxy::trigger('product.validation', new ValidationEvent($metadata));
      }
   }
