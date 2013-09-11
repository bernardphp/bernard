<?php

namespace Bernard\Middleware;

use Bernard\Middleware;
use Psr\Log\LoggerInterface;

/**
 * @package Bernard
 */
class LoggerFactory
{
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param  Middleware       $next
     * @return LoggerMiddleware
     */
    public function __invoke(Middleware $next)
    {
        return new LoggerMiddleware($next, $this->logger);
    }
}
