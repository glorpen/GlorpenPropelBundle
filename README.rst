-------------------
GlorpenPropelBundle
-------------------

Additional integration Propel with Symfony2.

TODO
----

- Importing config.yml & config_dev.yml to main configuration
- rollback and commit events


How to install
--------------

- add requirements to composer.json:

.. sourcecode:: json

   {
       "require": {
           "glorpen/propel-bundle": "@dev"
       },
       "repositories": [
           {
               "type": "hg",
               "url": "https://bitbucket.org/glorpen/glorpenpropelbundle"
           }
        ]
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
               new Glorpen\Propel\PropelBundle\\PropelBundle(),
               ...
           );
       }
    }


- add behavior configuration to propel config

You can do it by hand or by importing *PropelBundle/Resources/config/config.yml* and *config_dev.yml* accordingly.


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

ContainerAwareInterface for model
---------------------------------

You can implement **ContainerAwareInterface** on your model to get access to *Container* through built-in service. Container is injected in *model.construct* event.

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
