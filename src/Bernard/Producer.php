<?php

namespace Bernard;

use Bernard\Middleware\MiddlewareChain;
use Bernard\Message;
use Bernard\Message\Envelope;
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
    public function __construct(QueueFactory $queues, MiddlewareChain $middleware)
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

        $this->middleware->chain($queue)
            ->call(new Envelope($message));
    }
}
