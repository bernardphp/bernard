<?php

namespace Bernard\Middleware;

use Bernard\Envelope;
use Bernard\Middleware;
use Bernard\Queue;

/**
 * Uses `error_log` to stream error logs into the SAPI. This is
 * useful for examples.
 *
 * @package Bernard
 */
class ErrorLogMiddleware implements Middleware
{
    /**
     * @param Middleware $next
     */
    public function __construct(Middleware $next)
    {
        $this->next = $next;
    }

    /**
     * {@inheritDoc}
     */
    public function call(Envelope $envelope, Queue $queue)
    {
        try {
            $this->next->call($envelope, $queue);
        } catch (\Exception $e) {
            error_log(sprintf('[Bernard] Received exception "%s" with "%s" while processing "%s".',
                get_class($e), $e->getMessage(), $envelope->getName()));

            throw $e;
        }
    }
}
