<?php

namespace Raekke\Message;

/**
 * @package Raekke
 */
abstract class Message implements MessageInterface
{
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        foreach ($parameters as $k => $v) {
            $this->$k = $v;
        }
    }

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
