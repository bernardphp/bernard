<?php

declare(strict_types=1);

namespace Bernard;

/**
 * Wraps a Message with metadata that can be used for automatic retry
 * or inspection.
 */
final class Envelope
{
    private $message;
    private $class;
    private $timestamp;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->class = $message::class;
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
