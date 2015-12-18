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
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->class = get_class($message);
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
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
