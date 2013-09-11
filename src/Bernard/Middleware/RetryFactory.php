<?php

namespace Bernard\Middleware;

use Bernard\Middleware;
use Bernard\QueueFactory;

/**
 * @package Bernard
 */
class RetryFactory
{
    protected $queues;

    /**
     * @param QueueFactory $queues
     */
    public function __construct(QueueFactory $queues)
    {
        $this->queues = $queues;
    }

    /**
     * @param Middleware $next
     * @return RetryMiddleware
     */
    public function __invoke(Middleware $next)
    {
        return new RetryMiddleware($next, $this->queues);
    }
}
