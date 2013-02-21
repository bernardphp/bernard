<?php

namespace Raekke\Serializer;

use Raekke\Message\MessageWrapper;

/**
 * @package Raekke
 */
interface SerializerInterface
{
    /**
     * @param MessageWrapper $message
     * @return string
     */
    public function serialize(MessageWrapper $message);

    /**
     * @return MessageWrapper
     */
    public function deserialize($serialized);
}
