<?php

namespace Raekke\Serializer;

use Raekke\Message\Envelope;
use JMS\Serializer\SerializerInterface as JMSSerializerInterface;
use JMS\Serializer\SerializationContext;

/**
 * @package Raekke
 */
class Serializer implements SerializerInterface
{
    public function __construct(JMSSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serialize(Envelope $message)
    {
        $context = SerializationContext::create()
            ->setSerializeNull(true);

        return $this->serializer->serialize($message, 'json', $context);
    }

    public function deserialize($deserialized)
    {
        return $this->serializer->deserialize($deserialized, 'Raekke\Message\Envelope', 'json');
    }
}
