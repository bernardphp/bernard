<?php

namespace Bernard\EventDispatcher;

use Bernard\Envelope;
use Bernard\Queue;
use Bernard\EventDispatcher;
use Bernard\QueueFactory;

class FailureSubscriber implements EventSubscriber
{
    protected $queues;
    protected $name;

    public function __construct(QueueFactory $queues, $name = 'failed')
    {
        $this->queues = $queues;
        $this->name = $name;
    }

    public function onException(Envelope $envelope, Queue $queue, $exception)
    {
        $queue->acknowledge($envelope);

        $this->queues->create($this->name)->enqueue($envelope);
    }

    public function subscribe(EventDispatcher $dispatcher)
    {
        $dispatcher->on('bernard.exception', array($this, 'onException'));
    }
}
