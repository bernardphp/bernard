<?php

declare(strict_types=1);

namespace Bernard\Event;

use Bernard\Envelope;
use Bernard\Queue;

class RejectEnvelopeEvent extends EnvelopeEvent
{
    protected $exception;

    /**
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
