<?php

namespace Bernard\ServiceResolver;

use Bernard\Verify;
use Bernard\Envelope;

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
        Verify::isCallable($callable);

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

        // This is a bit slow, but only way to support on 5.3 if callable is not
        // a string.
        call_user_func($this->callable, $envelope->getMessage());
    }
}
