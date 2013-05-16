<?php

namespace Bernard\Tests\Serializer;

use Bernard\Serializer\JMSSerializer;
use Bernard\JMSSerializer\EnvelopeHandler;
use JMS\Serializer\SerializerBuilder;

class JMSSerializerTest extends AbstractSerializerTest
{
    public function createSerializer()
    {
        $class = new \ReflectionClass('Bernard\Serializer');
        $builder = new SerializerBuilder();
        $builder->configureHandlers(function ($registry) {
            $registry->registerSubscribingHandler(new EnvelopeHandler);
        });

        return new JMSSerializer($builder->build());
    }
}
