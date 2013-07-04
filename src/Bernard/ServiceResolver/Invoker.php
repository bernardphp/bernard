<?php

namespace Bernard\ServiceResolver;

use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
class Invoker
{
    protected $object;
    protected $envelope;

    /**
     * @param object $object
     */
    public function __construct($object, Envelope $envelope)
    {
        $this->object  = $object;
        $this->envelope = $envelope;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return 'on' . ucfirst($this->envelope->getName());
    }

    /**
     * Calls the correct method on the object if exists otherwise
     * it should throw some kind of exception.
     * The exception should be catched and send back to the parent which can then
     * retry or mark the message as failed.
     *
     * @throws Exception
     * @throws ReflectionException
     */
    public function invoke()
    {
        $method = new \ReflectionMethod($this->object, $this->getMethodName());
        $method->invoke($this->object, $this->envelope->getMessage());
    }
}
