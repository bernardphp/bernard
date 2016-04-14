Producing messages
==================

Any message sent to Bernard must be an instance of ``Bernard\Message``,
which has a ``getName`` and ``getQueue`` method. ``getName`` is used when working on
messages and identifies the worker service that should work on it.

A message is given to a producer that sends the message to the right queue.
It is also possible to get the queue directly from the queue factory and push
the message there. But remember to wrap the message in an ``Envelope`` object.
The easiest way is to give it to the producer, as the queue name
is taken from the message object.

To make it easier to send messages and not require every type to be implemented
in a separate class, a ``Bernard\Message\DefaultMessage`` is provided. It can hold
any number of properties and only needs a name for the message. The queue name
is then generated from that. When generating the queue name it will insert a "_"
before any uppercase letter and then lowercase the name.

.. code-block:: php

    <?php

    use Bernard\Message\DefaultMessage;
    use Bernard\Producer;
    use Bernard\QueueFactory\PersistentFactory;
    use Bernard\Serializer;

    //.. create $driver
    $factory = new PersistentFactory($driver, new Serializer());
    $producer = new Producer($factory);

    $message = new DefaultMessage('SendNewsletter', array(
        'newsletterId' => 12,
    ));

    $producer->produce($message);

