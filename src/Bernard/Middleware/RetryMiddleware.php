<?php

namespace Bernard\Middleware;

use Bernard\Middleware;
use Bernard\QueueFactory;
use Bernard\Envelope;
use Exception;

/**
 * @package Bernard
 */
class RetryMiddleware
{
    const RETRIES = 5;

    protected $queues;
    protected $next;

    /**
     * @param Middleware   $next
     * @param QueueFactory $queues
     */
    public function __construct(Middleware $next, QueueFactory $queues)
    {
        $this->next = $next;
        $this->queues = $queues;
    }

    /**
     * {@inheritDoc}
     */
    public function call(Envelope $envelope)
    {
        try {
            $this->next->call($envelope);
        } catch (Exception $e) {
            $this->fail($envelope, $e);

            throw $e;
        }
    }

    /**
     * Moves the Envelope to failed queue if retries have been exhausted.
     *
     * @param Envelope  $envelope
     * @param Exception $e
     */
    protected function fail(Envelope $envelope, Exception $e)
    {
        if ($envelope->getRetries() < self::RETRIES) {
            // increment retry value
            $envelope->incrementRetries();

            return $this->queues->create($envelope->getMessage()->getQueue())->enqueue($envelope);
        }

        // The Message have been retried 5 times. Move it to the $failed queue.
        // the middleware chain is dropped.
        return $this->queues->create('failed')->enqueue($envelope);
    }
}
