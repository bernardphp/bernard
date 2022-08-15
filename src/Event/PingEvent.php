<?php

namespace Bernard\Event;

use Bernard\Queue;
use Symfony\Contracts\EventDispatcher\Event;

class PingEvent extends Event
{
    protected $queue;

    /**
     * @param Queue $queue
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
