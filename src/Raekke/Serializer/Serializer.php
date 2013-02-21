<?php

namespace Raekke\Serializer;

use Raekke\Message\MessageWrapper;
use JMS\Serializer\SerializerInterface;

/**
 * @package Raekke
 */
class Serializer
{
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serialize(MessageWrapper $wrapper)
    {
        return $this->serializer->serialize($wrapper, 'json');
    }

    public function deserialize($content)
    {
        return $this->serializer->deserialize($content, 'Raekke\Message\MessageWrapper', 'json');
    }
}
