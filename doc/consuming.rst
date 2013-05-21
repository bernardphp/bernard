Consuming Messages
==================

A single message represents a job that needs to be performed, and as described
earlier, a message's name is used to determine which service object should
receive that message.

A service object can be any object that has a method corresponding to the message
name prefixed with ``on``. So ``new DefaultMessage('SendNewsletter')`` will trigger a
call to ``$serviceObject->onSendNewsletter($message)``. For the system to know which service
object should handle which messages, your are required to register them first.

.. code-block:: php

    <?php

    use Bernard\ServiceResolver\ObjectResolver;
    use Bernard\Consumer;

    // .. create driver and a queuefactory
    // NewsletterMessageHandler is a pseudo service object that responds to
    // onSendNewsletter.

    $serviceResolver = new ObjectResolver;
    $serviceResolver->register('SendNewsletter', new NewsletterMessageHandler);

    // Bernard also comes with a service resolver for Pimple (Silex) which allows you
    // to use service ids and have your service object lazy loader.
    //
    // $serviceResolver = new \Bernard\Pimple\PimpleAwareResolver($pimple);
    // $serviceResolver->register('SendNewsletter', 'my.service.id');
    //
    // Symfony DependencyInjection component is also supported.
    //
    // $serviceResolver = new \Bernard\Symfony\ContainerAwareServiceResolver($container);
    // $serviceResolver->register('SendNewsletter', 'my.service.id');

    // Create a Consumer and start the loop. The second argument is optional and
    // is the queue failed messages should be added to. The last argument (array) is also optional
    // and the defaults can be seen in the Consumer class.
    $consumer = new Consumer($serviceResolver);
    $consumer->consume($queueFactory->create('send-newsletter'), $queueFactory->create('failed'), array(
        'max-runtime' => 900,
        'max-retries' => 5,
    ));

Commandline Interface
---------------------

Bernard comes with a ``ConsumeCommand`` which can be used with Symfony Console 
component.

.. code-block:: php

    <?php

    use Bernard\Symfony\Command\ConsumeCommand;

    // create $console application
    $console->add(new ConsumeCommand($services, $queueManager));

It can then be used as any other console command. The argument given should be
the queue that your messages are on. If we use the earlier example with sending
a newsletter, it would look like this.

.. code-block:: bash

    $ /path/to/console bernard:consume 'send-newsletter'

