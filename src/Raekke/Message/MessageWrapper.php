<?php

namespace Raekke\Message;

/**
 * Wraps a MessageInterface with metadata that can be used for automatic retry
 * or inspection.
 *
 * @package Raekke
 */
class MessageWrapper
{
    protected $message;
    protected $name;
    protected $class;
    protected $timestamp;
    protected $retries = 0;

    /**
     * @param MessageInterface $message
     */
    public function __construct(MessageInterface $message)
    {
        $this->message   = $message;
        $this->class     = get_class($message);
        $this->name      = $message->getName();
        $this->timestamp = time();
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return integer
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return integer
     */
    public function getRetries()
    {
        return $this->retries;
    }
}
