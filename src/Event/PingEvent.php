<?php

declare(strict_types=1);

namespace Bernard\Event;

use Bernard\Queue;
use Symfony\Component\EventDispatcher\Event;

class PingEvent extends Event
{
    protected $queue;

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
