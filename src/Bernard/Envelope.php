<?php

namespace Bernard;

/**
 * Wraps a Message with metadata that can be used for automatic retry
 * or inspection.
 *
 * @package Bernard
 */
class Envelope
{
    protected $message;
    protected $class;
    protected $timestamp;

    /**
     * @param Message $message
     * @param string $class
     * @param integer $timestamp
     */
    public function __construct(Message $message, $class, $timestamp)
    {
        $this->message   = $message;
        $this->class     = $class;
        $this->timestamp = $timestamp;
    }

    /**
     * @return Message
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
        return $this->message->getName();
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
}
