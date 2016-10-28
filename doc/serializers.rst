Serializers
===========

Bernard uses the `Symfony Serializer Component <http://symfony.com/doc/current/components/serializer.html>`_ to
serialize messages as JSON for persistent storage.

Default serializer
------------------

By default Bernard can handle serializing the ``Bernard\Envelope`` and ``Bernard\Message\PlainMessage`` classes,
which should be enough when you are just starting out:

.. code-block:: php

    <?php

    use Bernard\Serializer;

    $serializer = new Serializer();
    $json = $serializer->serialize($envelope);


Adding normalizers
------------------

If you are using your own custom message classes, you **must** provide a normalizer for them. This example assumes your
message contains getters and setters for the properties it needs serializing:

.. code-block:: php

    <?php

    use Bernard\Normalizer\PlainMessageNormalizer;
    use Bernard\Normalizer\EnvelopeNormalizer;
    use Bernard\Serializer;
    use Normalt\Normalizer\AggregateNormalizer;
    use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

    $aggregateNormalizer = new AggregateNormalizer([
        new EnvelopeNormalizer(),
        new GetSetMethodNormalizer(),
        new PlainMessageNormalizer(),
    ]);

    $serializer = new Serializer($aggregateNormalizer);
    $envelope = $serializer->deserialize($json);


The ``AggregateNormalizer`` will check each normalizer passed to its constructor and use the first one that can handle
the object given to it. You should always pass the ``EnvelopeNormalizer`` first. And it's a good idea to add the
``PlainMessageNormalizer`` last as a fallback when none other match.

More normalizers are available from `Symfony <http://symfony.com/doc/current/components/serializer.html#normalizers>`_,
along with the ``DoctrineNormalizer`` and ``RecursiveReflectionNormalizer`` from
`Normalt <https://github.com/bernardphp/normalt>`_.
