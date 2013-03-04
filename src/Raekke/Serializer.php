<?php

namespace Raekke;

use Raekke\Message\Envelope;

/**
 * @package Raekke
 */
interface Serializer
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
