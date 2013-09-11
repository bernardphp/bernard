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
    protected $name;

    /**
     * @param QueueFactory $queues
     * @param string       $name
     */
    public function __construct(QueueFactory $queues, $name = 'failed')
    {
        $this->queues = $queues;
        $this->name = $name;
    }

    /**
     * @param  Middleware      $next
     * @return RetryMiddleware
     */
    public function __invoke(Middleware $next)
    {
        return new RetryMiddleware($next, $this->queues, $this->name);
    }
}
