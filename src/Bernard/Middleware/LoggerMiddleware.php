<?php

namespace Bernard\Middleware;

use Bernard\Envelope;
use Bernard\Middleware;
use Bernard\Queue;
use Psr\Log\LoggerInterface;

/**
 * Sends useful logging messages to $logger.
 *
 * @package Bernard
 */
class LoggerMiddleware implements Middleware
{
    /**
     * @param Middleware      $next
     * @param LoggerInterface $logger
     */
    public function __construct(Middleware $next, LoggerInterface $logger)
    {
        $this->next = $next;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function call(Envelope $envelope, Queue $queue)
    {
        $context = array(
            'name' => $envelope->getName(),
        );

        try {
            $this->logger->info('[Bernard] Processing message "{name}".', $context);
            $this->next->call($envelope, $queue);

        } catch (\Exception $exception) {
            $context += compact('exception');
            $this->logger->error('[Bernard] Received exception while processing "{name}".', $context);

            throw $exception;
        }
    }
}
