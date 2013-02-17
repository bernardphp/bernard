<?php

namespace Raekke\Message;

/**
 * @package Raekke
 */
class Serializer
{
    /**
     * @param AbstactMessage $message
     * @return string
     */
    public function encode(AbstractMessage $message)
    {
        return serialize($message);
    }

    /**
     * @param string $string
     * @return AbstractMessage
     */
    public function decode($string)
    {
        return unserialize($string);
    }
}
