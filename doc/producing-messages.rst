Producing Messages
------------------

Bernard is designed around the Producer-Consumer pattern. Which is suited to queues and workers. So a Producer
sends the message onto a queue by convention of the Message instance given.

To start producing messages a Producer is needed:

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

    Message properties must always be simple values like numbers, strings or even array of simple values. This is because
    they are serialized when using a persisted connection.

In the example a number of different things happens. The Message is routed to a queue based on the Message object.
For getting started the DefaultMessage is a good fit. It translates the given name into a queue name ``send-newsletter``
and can hold an arbitrary number of properties.

A queue can hold different message types. A queue is therefor a prioritized list of messages that needs to get
consumed.
