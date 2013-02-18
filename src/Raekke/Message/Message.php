<?php

namespace Raekke\Message;

/**
 * @package Raekke
 */
abstract class Message implements MessageInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return end(explode('\\', get_class()));
    }

    /**
     * {@inheritDoc}
     */
    public function getQueue()
    {
        return trim(strtolower(preg_replace('/[A-Z]/', '-\\0', $this->getName())), '-');
    }
}
