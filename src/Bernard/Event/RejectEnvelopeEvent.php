<?php

namespace Bernard\Event;

use Bernard\Envelope;
use Bernard\Queue;

class RejectEnvelopeEvent extends EnvelopeEvent
{
    /**
     * @var Exception
     */
    protected $exception;

    /**
     * @param Envelope  $envelope
     * @param Queue     $queue
     * @param Exception $exception
     */
    public function __construct(Envelope $envelope, Queue $queue, \Exception $exception)
    {
        parent::__construct($envelope, $queue);

        $this->exception = $exception;
    }

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
