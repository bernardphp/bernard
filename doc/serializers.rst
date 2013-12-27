Serializers
===========

Bernard supports multiple serializers to serialize messages for persistent
storage, such as Predis, Redis and so on. So far, the examples have been using
JMS as it was the first supported.

The JMS Serializer is only recommended if you already use this serializer or
if you need advanced serialization in your application.

Naive
-----

Bernard ships with a Naive serializer, which only supported using
``DefaultMessage`` messages. And as such it should only be used when starting up
or playing with Bernard.

.. code-block:: php

    <?php

    use Bernard\Serializer\NaiveSerializer;

    $serializer = new NaiveSerializer();


Symfony Serializer Component
----------------------------

You can also use Bernard with the
the `Symfony Serializer Component <http://symfony.com/doc/current/components/serializer.html>`_.
It is important that the serializer uses ``Bernard\Symfony\EnvelopeNormalizer``
and the ``JsonEncoder`` to being able to serialize and deserialize messages.

.. warning::

    If you are using ``Bernard\Message\DefaultMessage``, you **must** also register
    ``Bernard\Symfony\DefaultMessageNormalizer`` for proper serialization /
    deserialization of message. This is strongly encouraged as it is the
    fallback when message classed cannot be found.

.. code-block:: json

    {
        "require" : {
            "symfony/serializer" : "~2.2"
        }
    }

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

As already said, you can also use the
`JMS Serializer <http://jmsyst.com/libs/serializer>`_. This is only possible
when the subscribing handler has been added.

.. code-block:: json

    {
        "require" : {
            "jms/serializer" : "0.13.0@dev"
        }
    }

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
