<?php

namespace Bernard\Tests\Serializer;

use Bernard\Serializer\JMSSerializer;
use JMS\Serializer\SerializerBuilder;

class JMSSerializerTest extends AbstractSerializerTest
{
    public function createSerializer()
    {
        $class = new \ReflectionClass('Bernard\Serializer');
        $builder = new SerializerBuilder();
        $builder->addMetadataDir(dirname($class->getFilename()) . '/Resources/serializer', 'Bernard');

        return new JMSSerializer($builder->build());
    }
}
