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
     * @param string $usage Either "consumer" or "producer"
     */
    public function __construct(Middleware $next, LoggerInterface $logger, $usage = 'consumer')
    {
        $this->next = $next;
        $this->logger = $logger;
        $this->usage = $usage;
    }

    /**
     * {@inheritDoc}
     */
    public function call(Envelope $envelope)
    {
        if ($this->usage == 'producer') {
            $this->logger->info('[Bernard] Produced "{message}" for "{queue}".', array(
                'name' => $envelope->getName(),
                'queue' => $envelope->getMessage()->getQueue(),
            ));

            return;
        }

        $this->logger->info('[Bernard] Processing message "{queue}" since {time} [{name}]".', array(
            'queue' => $envelope->getMessage()->getQueue(),
            'name' => $envelope->getName(),
            'time' => time(),
        ));

        try {
            $this->next->call($envelope);
        } catch (\Exception $e) {
            $this->logger->error('[Bernard] Received exception while processing "{name}".', array(
                'name' => $envelope->getName(),
                'exeception' => $e,
            ));

            throw $e;
        }
    }
}
