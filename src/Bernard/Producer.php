<?php

namespace Bernard;

use Bernard\Middleware\MiddlewareBuilder;
use Bernard\Message;
use Bernard\Envelope;
use Bernard\QueueFactory;

/**
 * @package Bernard
 */
class Producer
{
    protected $queues;
    protected $middleware;

    /**
     * @param QueueFactory $factory
     */
    public function __construct(QueueFactory $queues, MiddlewareBuilder $middleware)
    {
        $this->queues = $queues;
        $this->middleware = $middleware;
    }

    /**
     * {@inheritDoc}
     */
    public function produce(Message $message)
    {
        $queue = $this->queues->create($message->getQueue());

        $this->middleware->build($queue)
            ->call(new Envelope($message));
    }
}
