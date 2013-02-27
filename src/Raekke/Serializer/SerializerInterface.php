<?php

namespace Raekke\Serializer;

use Raekke\Message\Envelope;

/**
 * @package Raekke
 */
interface SerializerInterface
{
    /**
     * @param  Envelope $message
     * @return string
     */
    public function serialize(Envelope $message);

    /**
     * @return Envelope
     */
    public function deserialize($serialized);
}
