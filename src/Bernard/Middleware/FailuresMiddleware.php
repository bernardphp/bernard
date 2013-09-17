<?php

namespace Bernard\Middleware;

use Bernard\Middleware;
use Bernard\QueueFactory;
use Bernard\Queue;
use Bernard\Envelope;
use Exception;

/**
 * @package Bernard
 */
class FailuresMiddleware implements Middleware
{
    protected $queues;
    protected $next;
    protected $name;

    /**
     * @param Middleware   $next
     * @param QueueFactory $queues
     * @param string       $name
     */
    public function __construct(Middleware $next, QueueFactory $queues, $name = 'failed')
    {
        $this->next = $next;
        $this->queues = $queues;
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function call(Envelope $envelope, Queue $queue)
    {
        try {
            $this->next->call($envelope, $queue);
        } catch (Exception $e) {
            // Acknowledge are only done when a Envelope is processed with out interruption. but since
            // it is getting requeued we dont want it retried.
            $queue->acknowledge($envelope);

            $this->queues->create($this->name)->enqueue($envelope);

            throw $e;
        }
    }
}
