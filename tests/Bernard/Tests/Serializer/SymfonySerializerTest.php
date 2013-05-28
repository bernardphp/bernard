<?php

namespace Bernard\Tests\Serializer;

use Bernard\Serializer\SymfonySerializer;
use Bernard\Symfony\DefaultMessageNormalizer;
use Bernard\Symfony\EnvelopeNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Serializer;

class SymfonySerializerTest extends AbstractSerializerTest
{
    public function createSerializer()
    {
        $normalizers = array(new EnvelopeNormalizer, new DefaultMessageNormalizer, new CustomNormalizer);

        return new SymfonySerializer(new Serializer($normalizers, array(new JsonEncoder)));
    }
}
