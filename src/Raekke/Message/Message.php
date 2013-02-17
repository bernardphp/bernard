<?php

namespace Raekke\Message;

/**
 * @package Raekke
 */
abstract class Message implements MessageInterface
{
    protected $header;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        foreach ($parameters as $k => $v) {
            $this->$k = $v;
        }

        $this->header = new MessageHeader;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader()
    {
        return $this->header;
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
        return strtolower(preg_replace('/[A-Z]/', '-\\0', $this->getName()));
    }
}
