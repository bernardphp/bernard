<?php

namespace Bernard\Message;

use Bernard\Message;

/**
 * Wraps a Message with metadata that can be used for automatic retry
 * or inspection.
 *
 * @package Bernard
 */
final class Envelope
{
    protected $message;
    protected $class;
    protected $timestamp;
    protected $retries = 0;

    /**
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message   = $message;
        $this->class     = get_class($message);
        $this->timestamp = time();
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

    /**
     * @return integer
     */
    public function getRetries()
    {
        return $this->retries;
    }

    /**
     * Increment number of retries
     */
    public function incrementRetries()
    {
        $this->retries += 1;
    }
}
