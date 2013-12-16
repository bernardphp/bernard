<?php

namespace Bernard\Tests\Serializer;

use Bernard\Serializer\SymfonySerializer;
use Bernard\Symfony\DefaultMessageNormalizer;
use Bernard\Symfony\EnvelopeNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class SymfonySerializerTest extends AbstractSerializerTestCase
{
    public function getFixturesDir()
    {
        return __DIR__ . '/../Fixtures/serializer';
    }

    public function getSerializer()
    {
        $serializer = new Serializer(array(new EnvelopeNormalizer, new DefaultMessageNormalizer), array(new JsonEncoder));

        return new SymfonySerializer($serializer);
    }
}
