-----------------
PropelEventBundle
-----------------

Propel events in Symfony2.


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

- add behavior to propel config
::

     propel:
        build_properties:
          propel.behavior.event.class: 'src.Glorpen.PropelEvent.PropelEventBundle.behavior.EventBehavior'
          propel.behavior.default: "event"

