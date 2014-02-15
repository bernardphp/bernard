<?php

namespace Bernard\Event;

use Bernard\Envelope;
use Bernard\Queue;

class EnvelopeEvent extends \Symfony\Component\EventDispatcher\Event
{
    protected $envelope;
    protected $queue;

    public function __construct(Envelope $envelope, Queue $queue)
    {
        $this->envelope = $envelope;
        $this->queue = $queue;
    }

    public function getEnvelope()
    {
        return $this->envelope;
    }

    public function getQueue()
    {
        return $this->queue;
    }
}
