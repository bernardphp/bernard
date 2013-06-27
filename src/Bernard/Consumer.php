<?php

namespace Bernard;

use Bernard\Message\Envelope;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Exception;

declare(ticks=1);

/**
 * @package Consumer
 */
class Consumer
{
    protected $services;
    protected $logger;
    protected $shutdown = false;
    protected $options = array(
        'max-retries' => 5,
        'max-runtime' => PHP_INT_MAX,
    );

    /**
     * @param ServiceResolver $services
     */
    public function __construct(ServiceResolver $services, LoggerInterface $logger = null)
    {
        if (!$logger) {
            $logger = new NullLogger;
        }

        $this->services = $services;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function consume(Queue $queue, Queue $failed = null, array $options = array())
    {
        $this->bind();
        $this->configure($options);

        while (microtime(true) < $this->options['max-runtime'] && !$this->shutdown) {
            if (!$envelope = $this->queue->dequeue($queue)) {
                continue;
            }

            $this->invoke($envelope);
        }
    }

    /**
     * Mark Consumer as shutdown
     */
    public function shutdown()
    {
        $this->logger->debug('[Bernard] Received shutdown signal. Terminating...');

        $this->shutdown = true;
    }

    /**
     * @param Envelope $envelope
     */
    protected function invoke(Envelope $envelope)
    {
        try {
            $invocator = $this->services->resolve($envelope->getMessage());
            $invocator->invoke();
        } catch (Exception $e) {
            $this->fail($envelope, $e);
        }
    }

    /**
     * @param Envelope $envelope
     * @param Exception $exception
     * @param Queue|null $failed
     */
    protected function fail(Envelope $envelope, Exception $exception, Queue $failed = null)
    {
        $this->logger->warning('[Bernard] Exception occured while processing "{message}".', array(
            'exception' => $exception,
            'message'   => $envelope->getName(),
        ));

        if ($envelope->getRetries() < $this->options['max-retries']) {
            $envelope->incrementRetries();

           return $queue->enqueue($envelope);
        }

        if ($failed) {
            $failed->enqueue($envelope);
        }
    }

    /**
     * @param array $options
     */
    protected function configure(array $options)
    {
        $this->options = $this->options + array_filter($options);
        $this->options['max-runtime'] = microtime(true) + $this->options['max-runtime'];
    }

    /**
     * Setup signal handlers for unix signals.
     */
    protected function bind()
    {
        pcntl_signal(SIGTERM, array($this, 'shutdown'));
        pcntl_signal(SIGQUIT, array($this, 'shutdown'));
        pcntl_signal(SIGINT,  array($this, 'shutdown'));
    }
}
