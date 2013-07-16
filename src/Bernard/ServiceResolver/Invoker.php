<?php

namespace Bernard\ServiceResolver;

use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
class Invoker
{
    protected $object;

    /**
     * @param object $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * Calls the correct method on the object if exists otherwise
     * it should throw some kind of exception.
     * The exception should be catched and send back to the parent which can then
     * retry or mark the message as failed.
     *
     * @param  Envelope $envelope
     * @throws Exception
     */
    public function invoke(Envelope $envelope)
    {
        $method = $this->getMethodName($envelope);

        $this->object->$method($envelope->getMessage());
    }

    /**
     * @param Envelope $envelope
     * @return string
     */
    protected function getMethodName(Envelope $envelope)
    {
        return 'on' . ucfirst($envelope->getName());
    }
}
