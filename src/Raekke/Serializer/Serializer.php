<?php

namespace Raekke\Serializer;

use Raekke\Message\Envelope;
use JMS\Serializer\SerializerInterface as JMSSerializerInterface;

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
        return $this->serializer->serialize($message, 'json');
    }

    public function deserialize($deserialized)
    {
        return $this->serializer->deserialize($deserialized, 'Raekke\Message\Envelope', 'json');
    }
}
