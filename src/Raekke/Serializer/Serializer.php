<?php

namespace Raekke\Serializer;

use Raekke\Message\MessageWrapper;
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

    public function serialize(MessageWrapper $message)
    {
        return $this->serializer->serialize($message, 'json');
    }

    public function deserialize($deserialized)
    {
        return $this->serializer->deserialize($deserialized, 'Raekke\Message\MessageWrapper', 'json');
    }
}
