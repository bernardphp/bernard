Getting Started
===============

Installing
----------

The easiest way to install Bernard is by using `Composer <http://getcomposer.org>`_.
If your projects do not already use this, it is highly recommended to start.

.. code-block:: bash

    $ composer require bernard/bernard:0.6.0

Then look at what kind of drivers and serializers there is available and install their dependencies
before use.

Examples
--------

In the ``example`` directory there are two examples of running Bernard. ``producer.php`` will
connect to redis on localhost and produce ``EchoTime`` messages. ``consumer.php`` will consume
theese and print the timestamp.

``in_memory.php`` will produce 20 ``EchoTime`` messages and consume them right they
have been sent. It uses ``SplQueue`` and does not need a redis backend.

Producing Messages
------------------

Any message sent to Bernard must be an instance of ``Bernard\Message``
which have a ``getName`` and ``getQueue`` method. ``getName`` is used when working on
messages and identifies the worker service that should work on it.

A message is given to a producer that sends the message to the right queue.
It is also possible to get the queue directly from the queue factory and push
the message there. But remember to wrap the message in an ``Envelope`` object.
The easiest way is to give it to the producer as the queue name
is taken from the message object.

To make it easier to send messages and not require every type to be implemented
in a seperate class, a ``Bernard\Message\DefaultMessage`` is provided. It can hold
any number of proberties and only needs a name for the message. The queue name
is then generated from that. When generating the queue name it will insert a "_"
before any uppercase letters and then lowercase everything.

.. code-block:: php

    <?php

    use Bernard\Message\DefaultMessage;
    use Bernard\Producer;
    use Bernard\QueueFactory\PersistentFactory;
    use Bernard\Serializer\NaiveSerializer;

    // .. create $driver
    $factory = new PersistentFactory($driver, new NaiveSerializer);
    $producer = new Producer($factory);

    $message = new DefaultMessage("SendNewsletter", array(
        'newsletterId' => 12,
    ));

    $producer->produce($message);

In Memory Queues
~~~~~~~~~~~~~~~~

Bernard comes with an implemention for ``SplQueue`` which is completly in memory.
It is useful for development and/or testing, when you don't necessarily want actions to be
performed.
