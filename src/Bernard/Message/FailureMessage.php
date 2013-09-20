<?php

namespace Bernard\Message;

use Bernard\Message;

/**
 * @package Bernard
 */
class FailureMessage extends AbstractMessage
{
    protected $message;
    protected $name;
    protected $retries;

    /**
     * @param Message $message
     * @param int     $retries
     */
    public function __construct(Message $message, $retries)
    {
        $this->message = $message;
        $this->name = $message->getName();
        $this->retries = $retries;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getRetries()
    {
        return $this->retries;
    }

    /**
     * @return \Bernard\Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
