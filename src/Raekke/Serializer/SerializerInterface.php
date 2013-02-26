<?php

namespace Raekke\Serializer;

use Raekke\Message\MessageWrapper;

/**
 * @package Raekke
 */
interface SerializerInterface
{
    /**
     * @param  MessageWrapper $message
     * @return string
     */
    public function serializeWrapper(MessageWrapper $message);

    /**
     * @return MessageWrapper
     */
    public function deserializeWrapper($serialized);
}
