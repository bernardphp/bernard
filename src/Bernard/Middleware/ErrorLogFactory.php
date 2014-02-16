<?php

namespace Bernard\Middleware;

use Bernard\Middleware;

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
