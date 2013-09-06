<?php

namespace Bernard\ServiceResolver;

use Bernard\Assert;
use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
class Invoker implements \Bernard\Middleware
{
    protected $callable;

    /**
     * @param callable $callable
     */
    public function __construct($callable)
    {
        Assert::assertCallable($callable);

        $this->callable = $callable;
    }

    /**
     * @see Invoker::call()
     */
    public function invoke(Envelope $envelope)
    {
        $this->call($envelope);
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
    public function call(Envelope $envelope)
    {
        $callable = $this->callable;
        $callable($envelope->getMessage());
    }
}
