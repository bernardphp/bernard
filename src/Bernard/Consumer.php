<?php

namespace Bernard;

use Bernard\Message\Envelope;
use Exception;

declare(ticks=1);

/**
 * @package Consumer
 */
class Consumer
{
    protected $services;
    protected $shutdown = false;
    protected $options = array(
        'max-retries' => 5,
        'max-runtime' => PHP_INT_MAX,
    );

    /**
     * @param ServiceResolver $services
     */
    public function __construct(ServiceResolver $services)
    {
        $this->services = $services;
    }

    /**
     * {@inheritDoc}
     */
    public function consume(Queue $queue, Queue $failed = null, array $options = array())
    {
        $this->bind();
        $this->configure($options);

        while (microtime(true) < $this->options['max-runtime'] && !$this->shutdown) {
            if ($envelope = $queue->dequeue()) {
                $this->invoke($envelope, $queue, $failed);
            }
        }
    }

    /**
     * Mark Consumer as shutdown
     */
    public function shutdown()
    {
        $this->shutdown = true;
    }

    /**
     * @param Envelope $envelope
     */
    protected function invoke(Envelope $envelope, Queue $queue, Failed $failed = null)
    {
        try {
            $invocator = $this->services->resolve($envelope->getMessage());
            $invocator->invoke();
        } catch (Exception $e) {
            $this->fail($envelope, $e, $queue, $failed);
        }
    }

    /**
     * @param Envelope $envelope
     * @param Exception $exception
     * @param Queue|null $failed
     */
    protected function fail(Envelope $envelope, Exception $exception, Queue $queue, Queue $failed = null)
    {
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
