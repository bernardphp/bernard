Consuming Messages
==================

A single message represents a job that needs to be performed, and as described
earlier, a message's name is used to determine which service object should
receive that message.

A service object can be any object that has a method corresponding to the name of the
message with the first letter lower cased. So ``new DefaultMessage('SendNewsletter')`` will trigger a
call to ``$serviceObject->sendNewsletter($message)``. For the system to know which service
object should handle which messages, you are required to register them first.

.. code-block:: php

    <?php

    use Bernard\Router\SimpleRouter;
    use Bernard\Consumer;

    // .. create driver and a queuefactory
    // NewsletterMessageHandler is a pseudo service object that responds to
    // sendNewsletter.

    $router = new SimpleRouter();
    $router->add('SendNewsletter', new NewsletterMessageHandler);

    // Bernard also comes with a router for Pimple (Silex) which allows you
    // to use service ids and have your service object lazy loader.
    //
    // $router = new \Bernard\Router\PimpleAwareRouter($pimple);
    // $router->add('SendNewsletter', 'my.service.id');
    //
    // Symfony DependencyInjection component is also supported.
    //
    // $router = new \Bernard\Router\ContainerAwareRouter($container);
    // $router->add('SendNewsletter', 'my.service.id');

    // Create a Consumer and start the loop. The second argument is optional and is an array
    // of options. Currently only ``max-runtime`` is supported which specifies the max runtime
    // in seconds.
    $consumer = new Consumer($router, $eventDispatcher);
    $consumer->consume($queueFactory->create('send-newsletter'), array(
        'max-runtime' => 900,
    ));

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
