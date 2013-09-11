<?php

namespace Bernard\Middleware;

use Bernard\Message\Envelope;
use Bernard\Middleware;

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
    public function call(Envelope $envelope)
    {
        try {
            $this->next->call($envelope);
        } catch (\Exception $e) {
            error_log(sprintf('[Bernard] Receieved exception "%s" with "%s" while processing "%s".',
                get_class($e), $e->getMessage(), $envelope->getName()));

            throw $e;
        }
    }

    /**
     * Static that can be used as the callable for the builder.
     *
     * @param Middleware $next
     * @return self
     */
    public static function create(Middleware $next)
    {
        return new self($next);
    }
}
