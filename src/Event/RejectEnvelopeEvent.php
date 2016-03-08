<?php

namespace Bernard\Event;

use Bernard\Envelope;
use Bernard\Queue;

/**
 * @package Bernard
 */
class RejectEnvelopeEvent extends EnvelopeEvent
{
    protected $exception;

    /**
     * @param Envelope              $envelope
     * @param Queue                 $queue
     * @param \Exception|\Throwable $exception
     */
    public function __construct(Envelope $envelope, Queue $queue, $exception)
    {
        parent::__construct($envelope, $queue);

        $this->exception = $exception;
    }

    /**
     * @return \Exception|\Throwable
     */
    public function getException()
    {
        return $this->exception;
    }
}
