Getting Started
===============

Installation
------------

The easiest way to install Bernard is using `Composer <http://getcomposer.org>`_.
If your projects do not already use this, it is highly recommended to start
using it.

To install Bernard, run:

.. code-block:: bash

    $ composer require bernard/bernard:0.6.0

Then look at what kind of drivers and serializers there is available and install
the ones you like before you are going to use Bernard.

Examples
--------

There are numerous examples of running Bernard in the ``example`` directory. The files are
named after the driver they are using. Each file takes the argument ``consume`` or ``produce``.
For instance, to use the ``Predis`` use:

.. code-block:: sh

    $ php ./example/predis.php consume
    $ php ./example/predis.php produce

And you would see properly a lot of output showing an error. This is because
the ``ErrorLogMiddleware`` is registered and shows all exceptions. In this case,
the exception is caused by ``rand()`` always returning 7.

This directory is a good source for setting stuff up and can be used as a go to guide.

Producing Messages
------------------

Any message sent to Bernard must be an instance of ``Bernard\Message``,
which has a ``getName`` and ``getQueue`` method. ``getName`` is used when working on
messages and identifies the worker service that should work on it.

A message is given to a producer that sends the message to the right queue.
It is also possible to get the queue directly from the queue factory and push
the message there. But remember to wrap the message in an ``Envelope`` object.
The easiest way is to give it to the producer, as the queue name
is taken from the message object.

To make it easier to send messages and not require every type to be implemented
in a seperate class, a ``Bernard\Message\DefaultMessage`` is provided. It can hold
any number of proberties and only needs a name for the message. The queue name
is then generated from that. When generating the queue name it will insert a "_"
before any uppercase letter and then lowercase the name.

.. code-block:: php

    <?php

    use Bernard\Message\DefaultMessage;
    use Bernard\Producer;
    use Bernard\QueueFactory\PersistentFactory;
    use Bernard\Serializer\NaiveSerializer;

    // .. create $driver
    $factory = new PersistentFactory($driver, new NaiveSerializer());
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
