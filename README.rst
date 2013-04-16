-------------------
GlorpenPropelBundle
-------------------

Additional integration Propel with Symfony2.

How to install
==============

- add requirements to composer.json:

.. sourcecode:: json

   {
       "require": {
           "glorpen/propel-bundle": "@dev"
       }
   }
   

- enable the plugin in your **AppKernel** class

*app/AppKernel.php*

.. sourcecode:: php

    <?php
    
    class AppKernel extends AppKernel
    {
       public function registerBundles()
       {
           $bundles = array(
               ...
               new Glorpen\Propel\PropelBundle\GlorpenPropelBundle(),
               ...
           );
       }
    }


- add behavior configuration to propel config

To enable all behaviors at once you can import to your configuration *PropelBundle/Resources/config/config.yml* and *config_dev.yml* accordingly.


Propel Events
=============

If you didn't import *config.yml* providen by this bundle, you have to add *event* behavior to your propel configuration and change *PropelPDO* class.


.. sourcecode:: yaml

   propel:
     build_properties:
       propel.behavior.event.class: 'vendor.glorpen.propel-bundle.Glorpen.Propel.PropelBundle.Behaviors.EventBehavior'
       propel.behavior.default: "event"
     dbal:
       classname: Glorpen\Propel\PropelBundle\Connection\EventPropelPDO
 

And in *config_dev.yml*:

.. sourcecode:: yaml

   propel:
     dbal:
       classname: Glorpen\Propel\PropelBundle\Connection\EventDebugPDO


Listening for propel hooks
--------------------------

- register listener

.. sourcecode:: xml

	<service class="SomeBundle\Listeners\HistoryBehaviorListener">
		<argument type="service" id="security.context" />
		<tag name="propel.event" />
	</service>
	
	<service id="my.listener" class="SomeBundle\Listeners\HistoryBehaviorListener">
		<tag name="propel.event" method="onPropelEventSave" event="model.save.post" />
	</service>

Available events
----------------

- connection.create
- connection.commit.pre
- connection.commit.post
- connection.rollback.post
- connection.rollback.pre

Event class: `ConnectionEvent`

- model.insert.post
- model.update.post
- model.delete.post
- model.save.post
- model.insert.pre
- model.update.pre
- model.delete.pre
- model.save.pre
- model.construct

Event class: `ModelEvent`

- query.delete.pre
- query.delete.post
- query.select.pre
- query.select.post
- query.update.pre
- query.update.post
- query.construct

Event class: `QueryEvent`

- peer.construct

Event class: `PeerEvent`

- update.post
- delete.post
- update.pre
- delete.pre
- construct

Will be called on model/query/peer construct/delete/update/etc

ContainerAwareInterface for model
---------------------------------

You can implement **ContainerAwareInterface** on your model to get access to *Container* through built-in service. Container is injected in *model.construct* event.

If you find yourself with error like `Serialization of 'Closure' is not allowed` it is probably about some not serializable services injected in model (since propel occasionally serializes and unserializes data).

.. sourcecode:: php

   <?php
   
   use Symfony\Component\DependencyInjection\ContainerAwareInterface;
   use Symfony\Component\DependencyInjection\ContainerInterface;
   
   class Something extends BaseSomething implements ContainerAwareInterface
   {
      private $someService;
      
      public function setContainer(ContainerInterface $container = null){
         if($container) $this->someService = $this->container->get("some_service");
      }  
   }

Transaction events
------------------

Just like with Doctrine *@ORM\HasLifecycleCallbacks* you can handle non db logic in model in db transaction.

Commit hooks will be run just before PDO transaction commit and rollback just before rolback and only on saved models (if exception was thrown in preCommit hook). Methods provided by **EventBehavior** are:

- preCommit
- preCommitSave
- preCommitUpdate
- preCommitInsert
- preCommitDelete
- preRollback
- preRollbackSave
- preRollbackUpdate
- preRollbackInsert
- preRollbackDelete

Be aware that when using transaction on big amount of model objects with on-demand formatter they still will be cached inside service so you can exhaust available php memory. 

And example how you can use available hooks (code mostly borrowed from Symfony2 cookbook):

.. sourcecode:: php

   <?php
   class SomeModel extends BaseSomeModel {
      public function preCommitSave(\PropelPDO $con = null){
         $this->upload();
      }
      public function preCommitDelete(\PropelPDO $con = null){
         $this->removeUpload();
      }
      
      public function preSave(\PropelPDO $con = null){
         $this->preUpload();
         return parent::preSave($con);
      }
      
      // code below is copied from http://symfony.com/doc/2.1/cookbook/doctrine/file_uploads.html
      
      public $file;
      
      public function preUpload(){
         if (null !== $this->file){
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path = $filename.'.'.$this->file->guessExtension();
         }
      }
      
      public function upload(){
         if (null === $this->path) return;
      
         // if there is an error when moving the file, an exception will
         // be automatically thrown by move(). This will properly prevent
         // the entity from being persisted to the database on error
         $this->file->move($this->getUploadRootDir(), $this->path);
         throw new \RuntimeException("file cannot be saved");
      
         unset($this->path);
      }
      
      public function removeUpload(){
         if ($file = $this->getAbsolutePath()){
            unlink($file);
         }
      }
   }

Custom events
-------------

You can trigger events with generic or custom Event class, in following example **ValidationEvent**. 

- create **ValidationEvent** event

.. sourcecode:: php

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

.. sourcecode:: xml

   <service id="your.service" class="%your.service.class%">
      <argument>%your.service.argument%</argument>
      <tag name="propel.event" method="onProductLoadValidatorMetadata" event="product.validation" />
   </service>

- and then use it within model class

.. sourcecode:: php

   <?php
   
   namespace YourBundle\Model;
   use YourBundle\Events\ValidationEvent;
   use Glorpen\Propel\PropelBundle\\Dispatcher\EventDispatcherProxy;
   use Symfony\Component\Validator\Mapping\ClassMetadata;
   use YourBundle\Model\om\BaseProduct;
   
   class Product extends BaseProduct {
      public static function loadValidatorMetadata(ClassMetadata $metadata)
      {
         EventDispatcherProxy::trigger('product.validation', new ValidationEvent($metadata));
      }
   }


Model Extending
===============

If you didn't import *config.yml* providen by this bundle, you have to add *extend* behavior to your propel configuration.

.. sourcecode:: yaml

   propel:
     build_properties:
       propel.behavior.extend.class: 'vendor.glorpen.propel-bundle.Glorpen.Propel.PropelBundle.Behaviors.ExtendBehavior'
       propel.behavior.default: "extend"

Usage
-----

With behavior enabled you can define custom model classes for use with Propel. In *config.yml*:

.. sourcecode:: yaml

   glorpen_propel:
     extended_models:
       FOS\UserBundle\Propel\User: MyApp\MyBundle\Propel\User

You can extend only Model classes this way (extending Peers/Queries shouldn't be needed).

Calls to Query::find(), Peer::populateObject() etc. will now return your extended class objects.

In short it fixes:

-  extending Model classes used by other bundles (eg. FOSUserBundle)
-  queries/peer's returning proper isntances
-  creating proper Query instance when calling `SomeQuery::create()` 


FOSUserBundle and AdminGenerator
--------------------------------

With above config, you can generate backend with **AdminGenerator** for **FOSUser** edit/creation/etc. For now you have to create empty UserQuery and UserPeer classes and then whole backend for user model should work :)
