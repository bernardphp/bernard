<?php

namespace Bernard\Event;

use Bernard\Envelope;
use Bernard\Queue;

class RejectEnvelopeEvent extends EnvelopeEvent
{
    protected $exception;

    public function __construct(Envelope $envelope, Queue $queue, \Exception $exception)
    {
        parent::__construct($envelope, $queue);

        $this->exception = $exception;
    }

    public function getException()
    {
        return $this->exception;
    }
}
