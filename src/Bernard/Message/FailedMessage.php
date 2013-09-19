<?php

namespace Bernard\Message;

/**
 * @package Bernard
 */
class FailedMessage extends AbstractMessage
{
    protected $name;
    protected $retryCount = 0;

    /**
     * @param string $name
     * @param array  $parameters
     */
    public function __construct(\Bernard\Message $message)
    {
        foreach (get_object_vars($message) as $k => $v) {
            $this->$k = $v;
        }

        $this->name = $message->getName();
        $this->retryCount++;
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
    public function getRetryCount()
    {
        return $this->retryCount;
    }
}
