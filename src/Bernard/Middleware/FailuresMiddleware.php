<?php

namespace Bernard\Middleware;

use Bernard\Middleware;
use Bernard\QueueFactory;
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
    public function call(Envelope $envelope)
    {
        try {
            $this->next->call($envelope);
        } catch (Exception $e) {
            $this->queues->create($this->name)->enqueue($envelope);

            throw $e;
        }
    }
}
