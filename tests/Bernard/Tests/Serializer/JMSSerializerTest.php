<?php

namespace Bernard\Tests\Serializer;

use Bernard\JMSSerializer\EnvelopeHandler;
use Bernard\Serializer\JMSSerializer;
use JMS\Serializer\SerializerBuilder;
use Doctrine\Common\Annotations\AnnotationRegistry;

class JMSSerializerTest extends AbstractSerializerTest
{
    public function createSerializer()
    {
        AnnotationRegistry::registerLoader(current(spl_autoload_functions()));

        $builder = new SerializerBuilder();
        $builder->configureHandlers(function ($registry) {
            $registry->registerSubscribingHandler(new EnvelopeHandler);
        });

        return new JMSSerializer($builder->build());
    }
}
