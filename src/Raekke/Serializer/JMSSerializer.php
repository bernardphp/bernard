<?php

namespace Raekke\Serializer;

use Raekke\Message\Envelope;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

/**
 * @package Raekke
 */
class JMSSerializer implements \Raekke\Serializer
{
    protected $serializer;

    /**
     * @param JMSSerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize(Envelope $message)
    {
        $context = SerializationContext::create()
            ->setSerializeNull(true);

        return $this->serializer->serialize($message, 'json', $context);
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize($deserialized)
    {
        return $this->serializer->deserialize($deserialized, 'Raekke\Message\Envelope', 'json');
    }
}
