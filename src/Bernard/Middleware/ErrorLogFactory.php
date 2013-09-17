<?php

namespace Bernard\Middleware;

use Bernard\Middleware;
use Bernard\QueueFactory;

/**
 * @package Bernard
 */
class ErrorLogFactory
{
    /**
     * @param  Middleware         $next
     * @return ErrorLogMiddleware
     */
    public function __invoke(Middleware $next)
    {
        return new ErrorLogMiddleware($next);
    }
}
