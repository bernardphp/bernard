Getting Started
===============

Bernard is a framework for integration a message queue into your application.
It does this by providing a number of different connections for different backends like
Redis, Predis and others.

As it is a framework it not a plug-and-play library like php-resque.

Installation
------------

The only real way of installing is thruogh Composer. Create a ``composer.json`` file and run
``composer.phar henrikbjorn/bernard update``:

.. code-block:: json

    {
        "require" : {
            "henrikbjorn/bernard" : "~0.2"
        }
    }

Sending Messages
----------------

Bernard supports multiple connections. You have to choose the one suited for your backend or service.

After that create a Producer and start sending messages to your queue:

.. code-block:: php

    <?php

    use Bernard\Message\DefaultMessage;
    use Bernard\Producer;
    use Bernard\QueueFactory\InMemoryFactory;

    require_once 'vendor/autoload.php';

    $producer = new Producer(new InMemoryFactory());
    $producer->produce(new DefaultMessage('SendNewsletter', array(
        'email' => 'john.doe@acme.com',
    )));

.. warning::

    The above example uses an in memory connection. The messages sent there are not persisted and will
    not be available to the consumer unless the consumer runs in the same request.

