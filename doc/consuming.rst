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

Services
--------
