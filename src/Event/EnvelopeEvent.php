<?php

namespace Bernard\Event;

use Bernard\Envelope;
use Bernard\Queue;

class EnvelopeEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    protected $envelope;
    protected $queue;

    /**
     * @param Envelope $envelope
     * @param Queue    $queue
     */
    public function __construct(Envelope $envelope, Queue $queue)
    {
        $this->envelope = $envelope;
        $this->queue = $queue;
    }

    /**
     * @return Envelope
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }

    /**
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
