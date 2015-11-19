<?php

namespace Bernard\Event;

use Bernard\Envelope;
use Bernard\Queue;
use Symfony\Component\EventDispatcher;

/**
 * @package Bernard
 */
class PingEvent extends EventDispatcher\Event
{
    protected $queue;

    /**
     * @param Queue    $queue
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
