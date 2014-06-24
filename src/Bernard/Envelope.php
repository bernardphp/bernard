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
    protected $stamps;

    /**
     * @param Message $message
     */
    public function __construct(Message $message, array $stamps = array())
    {
        $this->message   = $message;
        $this->class     = get_class($message);
        $this->timestamp = time();
        $this->stamps    = array_filter($stamps, 'is_scalar');
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
     * @return array 
     */
    public function getStamps()
    {
        return $this->stamps;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getStamp($name, $default = null)
    {
        if (!isset($this->stamps[$name])) {
            return $default;
        }

        return $this->stamps[$name];
    }
}
