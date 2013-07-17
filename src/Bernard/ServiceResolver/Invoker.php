<?php

namespace Bernard\ServiceResolver;

use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
class Invoker
{
    protected $callable;

    /**
     * @param callable $callable
     */
    public function __construct($callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Expected argument of type "callable" but got "' . gettype($callable) . '".');
        }

        $this->callable = $callable;
    }

    /**
     * Calls the correct method on the object if exists otherwise
     * it should throw some kind of exception.
     * The exception should be catched and send back to the parent which can then
     * retry or mark the message as failed.
     *
     * @param  Envelope  $envelope
     * @throws Exception
     */
    public function invoke(Envelope $envelope)
    {
        call_user_func($this->callable, $envelope->getMessage());
    }
}
