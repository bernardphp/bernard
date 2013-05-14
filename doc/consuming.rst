Consuming
=========

Knowing how to get messages onto a queue. Getting them off it would be nice aswell. For this
there is a consume. It takes a QueueFactory and dequeues messages one by one and hands them over
to the correct service object:

.. code-block:: php

    <?php

    use Bernard\Consumer;
    use Bernard\ServiceResolver\ObjectResolver;
    use Bernard\QueueFactory\InMemoryFactory;

    $queues = new InMemoryFactory;
    $resolver = new ObjectResolver;
    $consumer = new Consumer($resolver);

    $consumer->consume($queues->create('send-newsletter'));

.. warning::

    As the others example this uses a InMemory queueing. Which means the queue will be empty in the above
    example.

This is the most basic setup of a Consumer. It works on a queue named ``send-newsletter``. And uses the ObjectResolver
to resolve messages to the correct php object.

There are a number of options for the consumer. It knows how to many times a message can be retried before being moved
to another queue. It knows for how long it may run.

Command line
------------

Bernard ships with a basic command for consuming. It is built on the Symfony Console component:

.. code-block:: php

    <?php

    use Bernard\Symfony\Command\ConsumeCommand;
    use Bernard\QueueFactory\InMemoryFactory;
    use Bernard\ServiceResolver\ObjectResolver;
    use Symfony\Component\Console\Application;

    require 'vendor/autoload.php';

    $cli = new Application;
    $cli->add(new ConsumeCommand(new ObjectResolver, new InMemoryFactory);
    $cli->run()

.. code-block:: bash

    $ /path/to/your/console bernard:consume queue-name

.. warning::

    The example above is an example and based on in memory queuing. Therefor nothing will happen and it runs
    forever without processing anything.

Services
--------

Messages are routed to a Service object which is a plain php object that have the method ``on{MessageName}``. The name
is normally the class name but with ``DefaultMessage`` it is the first argument to its constructor. Take the following
example where the message will call ``onSendNewsletter``:

.. code-block:: php

    <?php

    use Bernard\Consumer;
    use Bernard\Message\DefaultMessage;
    use Bernard\ServiceResolver\ObjectResolver;
    use Bernard\QueueFactory\InMemoryFactory;

    require 'vendor/autoload.php';

    class NewsletterService
    {
        public function onSendNewsletter(DefaultMessage $message)
        {
            print 'Received a message';
        }
    }

    $queues = new InMemoryFactory();

    $resolver = new ObjectResolver();
    $resolver->register('SendNewsletter', new NewsletterService());

    $consumer = new Consumer($resolver);
    $conusmer->consume($queues->create('send-newsletter'));


Now when a message is received from the ``send-newsletter`` queue you will see ``Received a message`` in your console.

Most of the time your service objects will have dependencies and be declared in a dependency injection container. Bernard
supports theese by default.

Pimple, Silex and Flint
^^^^^^^^^^^^^^^^^^^^^^^

Pimple is a super small and lightweight dependency injection container. Which can be used with Silex or Flint. And there
is no reason why thoose apps cant use a message queueing system:

.. code-block:: php

    <?php

    use Pimple;
    use Bernard\Pimple\PimpleAwareResolver;

    require 'vendor/autoload.php';

    $pimple = new Pimple();
    $resolver = new PimpleAwareResolver($pimple);
    $resolver->register('SendNewsletter', 'pimple_service_id');


Symfony Dependency Injection
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Symfony is a Full Stack framework for large applications. Currently there is not a bundle but it is super easy to
integrate by custom service declarations. Here is now it integrates with the dependency injection container:

.. code-block:: php

    <?php

    use Bernard\Symfony\ContainerAwareResolver;
    use Symfony\Component\DependencyInjection\Container;

    $container = new Container();
    $resolver = new ContainerAwareResolver($container);
    $resolver->register('SendNewsletter', 'container_service_id');
