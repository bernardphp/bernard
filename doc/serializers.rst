Serializers
===========

Bernard supports multiple serializers to serialize messages for persistent storage such as Predis, Redis and so
on. So far the examples have been using JMS as it was the first supported.

JMS Serializer is only recommended if you already uses this serializer or have advanced serialization needs in
your application.

Symfony Serializer Component
----------------------------

It is important that the serializer uses ``Bernard\Symfony\EnvelopeNormalizer`` and the ``JsonEncoder`` to being able
to serialize and deserialize messages.

.. code-block:: php

    <?php

    use Bernard\Serializer\SymfonySerializer;
    use Bernard\Symfony\EnvelopeNormalizer;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Serializer;

    $symfonySerializer = new Serializer(array(new EnvelopeNormalizer), array(new JsonEncoder);
    $serializer = new SymfonySerializer($symfonySerializer);

JMS Serializer
--------------

Using JMS Serializer is only possible when the subscribing handler have been added.

.. code-block:: php

    <?php

    use Bernard\Serializer\JMSSerializer;
    use Bernard\JMSSerializer\EnvelopeHandler;
    use JMS\Serializer\SerializerBuilder;

    $jmsSerializer = SerializerBuilder::create()
        ->configureHandlers(function ($registry) {
            $registry->registerSubscribingHandler(new EnvelopeHandler);
        })
        ->build()
    ;

    $serializer = new JMSSerializer($jmsSerializer);
