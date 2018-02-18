Consuming Messages
==================

Consuming messages has two requirements:

* the system needs to know how messages should be handled
* the system needs to provide extension points for certain events

The first requirement is fulfilled by message routing, the second is by the event dispatcher system.

.. code-block:: php

    <?php

    use Symfony\Component\EventDispatcher\EventDispatcher;

    // $router = see bellow
    $eventDispatcher = new EventDispatcher();

    // Create a Consumer and start the loop.
    $consumer = new Consumer($router, $eventDispatcher);

    // The second argument is optional and is an array
    // of options. Currently only ``max-runtime`` is supported which specifies the max runtime
    // in seconds.
    $consumer->consume($queueFactory->create('send-newsletter'), array(
        'max-runtime' => 900,
    ));


Routing
-------

A single message represents a job that needs to be performed, and as described
earlier, by default a message's name is used to determine which receiver should
receive that message.

A receiver can be any of the following:

* callable
* class with a static method with the name of the message with the first letter lower cased
* object with a method with the name of the message with the first letter lower cased
* object implementing the ``Bernard\Receiver`` interface


For the system to know which receiver should handle which messages, you are required to register them first.

.. code-block:: php

    <?php

    use Bernard\Router\ReceiverMapRouter;
    use Bernard\Consumer;

    // create driver and a queuefactory
    // NewsletterMessageHandler is a pseudo receiver that has a sendNewsletter method.

    $router = new ReceiverMapRouter([
        'SendNewsletter' => new NewsletterMessageHandler(),
    ]);


Message routing can also happen based on the message class instead of the message name.

.. code-block:: php

    <?php

    use Bernard\Router\ClassNameRouter;
    use Bernard\Consumer;

    // create driver and a queuefactory
    // NewsletterMessageHandler is a pseudo receiver that has a sendNewsletter method.
    // NewsletterMessage is a pseudo message

    $router = new ClassNameRouter([
        NewsletterMessage::class => new NewsletterMessageHandler(),
    ]);


In some cases the above described receiver rules might not be enough.
The provided router implementations also accept a receiver resolver which can be used for example to resolve
receivers from a Dependency Injection container. A good example for that is the PSR-11 container resolver
implementation that comes with this package.

.. code-block:: php

    <?php

    use Bernard\Router\ReceiverMapRouter;
    use Bernard\Router\ContainerReceiverResolver;
    use Bernard\Consumer;

    // create driver and a queuefactory
    // NewsletterMessageHandler is a pseudo receiver that has a sendNewsletter method.
    // $container = your PSR-11 compatible container

    $router = new ReceiverMapRouter(
        [
            'SendNewsletter' => NewsletterMessageHandler::class,
        ],
        new ContainerReceiverResolver($container),
    );


Commandline Interface
---------------------

Bernard comes with a ``ConsumeCommand`` which can be used with Symfony Console
component.

.. code-block:: php

    <?php

    use Bernard\Command\ConsumeCommand;

    // create $console application
    $console->add(new ConsumeCommand($consumer, $queueFactory));

It can then be used as any other console command. The argument given should be
the queue that your messages are on. If we use the earlier example with sending
a newsletter, it would look like this.

.. code-block:: bash

    $ /path/to/console bernard:consume send-newsletter


Internals
---------

When a message is dequeued it is also marked as invisible (if the driver supports this) and when the message have
been consumed then it will also be acknowledged. Some drivers have a timeout on the invisible state and will automatically
requeue a message after that time. Therefore it is important to have a timeout greater than it takes for you
to consume a single message.
