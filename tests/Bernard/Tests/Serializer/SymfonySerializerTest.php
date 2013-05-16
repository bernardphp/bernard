<?php

namespace Bernard\Tests\Serializer;

use Bernard\Serializer\SymfonySerializer;
use Bernard\Symfony\EnvelopeNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class SymfonySerializerTest extends AbstractSerializerTest
{
    public function createSerializer()
    {
        return new SymfonySerializer(new Serializer(array(new EnvelopeNormalizer), array(new JsonEncoder)));
    }
}
