<?php

namespace Bernard\ServiceResolver;

use Bernard\Message;

/**
 * @package Bernard
 */
class Invocator
{
    protected $object;
    protected $message;

    /**
     * @param object $object
     */
    public function __construct($object, Message $message)
    {
        $this->object  = $object;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return 'on' . ucfirst($this->message->getName());
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
        $method->invoke($this->object, $this->message);
    }

    /**
     * Makes it possible to have this being a callable.
     *
     * @see Invocator::invoke()
     */
    public function __invoke()
    {
        $this->invoke();
    }
}
