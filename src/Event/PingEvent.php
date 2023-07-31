<?php

declare(strict_types=1);

namespace Bernard\Event;

use Bernard\Queue;

class PingEvent extends \Symfony\Contracts\EventDispatcher\Event
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
