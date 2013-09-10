<?php

namespace Bernard\Middleware;

use Bernard\Message\Envelope;
use Bernard\Middleware;
use Psr\Log\LoggerInterface;

/**
 * Sends useful logging messages to $logger.
 *
 * @package Raven
 */
class LoggerMiddleware implements Middleware
{
    /**
     * @param Middleware $next
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
    public function call(Envelope $envelope)
    {
        $context = array(
            'queue' => $envelope->getMessage()->getQueue(),
            'name' => $envelope->getName(),
        );

        try {
            $this->logger->info('[Bernard] Processing message "{name}" from "{queue}".', $context);
            $this->next->call($envelope);

        } catch (\Exception $exception) {
            $context += compact('exception');
            $this->logger->error('[Bernard] Received exception while processing "{name}".', $context);

            throw $e;
        }
    }
}
