Serializers
===========

Bernard uses the `Symfony Serializer Component <http://symfony.com/doc/current/components/serializer.html>`_ to
serialize messages for persistent storage.

Default serializer
------------------

By default Bernard can handle serializing the ``Bernard\Envelope`` and ``Bernard\Message\DefaultMessage`` classes,
which should be enough when you are just starting out:

.. code-block:: php

    <?php

    use Bernard\Driver\FlatFileDriver;
    use Bernard\QueueFactory\PersistentFactory;
    use Bernard\Serializer;

    $serializer = new Serializer();
    $driver = new FlatFileDriver('/path/to/queue');
    $queue = new PersistentFactory($driver, $serializer);


Custom serializers
------------------

If you are using your own custom message classes, you **must** provide a normalizer for them. This example assumes your
message contains getters and setters for the properties it needs serializing:

.. code-block:: php

    <?php

    use Bernard\Driver\FlatFileDriver;
    use Bernard\Normalizer\DefaultMessageNormalizer;
    use Bernard\Normalizer\EnvelopeNormalizer;
    use Bernard\QueueFactory\PersistentFactory;
    use Bernard\Serializer;
    use Normalt\Normalizer\AggregateNormalizer;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

    $aggregateNormalizer = new AggregateNormalizer([
        new EnvelopeNormalizer(),
        new GetSetMethodNormalizer(),
        new DefaultMessageNormalizer(),
    ]);

    $serializer = new Serializer($aggregateNormalizer);
    $driver = new FlatFileDriver('/path/to/queue');
    $queue = new PersistentFactory($driver, $serializer);


The ``AggregateNormalizer`` will check each normalizer passed to it's constructor and use the first one that can handle
the object given to it. You should always pass the ``EnvelopeNormalizer`` first. And it's a good idea to add the
``DefaultMessageNormalizer`` last as a fallback when none other match.

If your normalization needs differ, more available from
`Symfony <http://symfony.com/doc/current/components/serializer.html#normalizers>`_, along with the
``DoctrineNormalizer`` and ``RecursiveReflectionNormalizer`` from `Normalt <https://github.com/bernardphp/normalt>`_.
