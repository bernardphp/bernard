<?php

namespace Bernard;

use Bernard\Middleware\MiddlewareBuilder;

/**
 * @package Bernard
 */
class Producer implements Middleware
{
    protected $queues;
    protected $middleware;

    /**
     * @param QueueFactory      $queues
     * @param MiddlewareBuilder $middleware
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
        $queue = $this->queues->create(bernard_guess_queue($message));

        $middleware = $this->middleware->build($this);
        $middleware->call(new Envelope($message), $queue);
    }

    /**
     * {@inheritDoc}
     */
    public function call(Envelope $envelope, Queue $queue)
    {
        $queue->enqueue($envelope);
    }
}
