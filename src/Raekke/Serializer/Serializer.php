<?php

namespace Raekke\Serializer;

use Raekke\Message\MessageInterface;

/**
 * @package Raekke
 */
class Serializer
{
    /**
     * @param MessageInterface $message
     */
    public function serialize(MessageInterface $message)
    {
        return serialize($message);
    }

    /**
     * @param string $content
     */
    public function deserialize($content)
    {
        return unserialize($content);
    }
}
