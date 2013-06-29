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

        while ($this->tick($queue, $failed)) {
            // NO op
        }
    }

    /**
     * Returns true do indicate it should be run again or false to indicate 
     * it should not be run again.
     *
     * @param Queue $queue
     * @param Queue|null $failed
     * @return boolean
     */
    public function tick(Queue $queue, Queue $failed = null)
    {
        if ($this->shutdown) {
            return false;
        }

        if (microtime(true) > $this->options['max-runtime']) {
            return false;
        }

        if (!$envelope = $queue->dequeue()) {
            return true;
        }

        try {
            $invocator = $this->services->resolve($envelope);
            $invocator->invoke();
        } catch (Exception $e) {
            $this->fail($envelope, $e, $queue, $failed);
        }

        return true;
    }

    /**
     * @param array $options
     */
    public function configure(array $options)
    {
        $this->options = array_filter($options) + $this->options;
        $this->options['max-runtime'] += microtime(true);
    }

    /**
     * Mark Consumer as shutdown
     */
    public function shutdown()
    {
        $this->shutdown = true;
    }

    /**
     * @param Envelope   $envelope
     * @param Exception  $exception
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
     * Setup signal handlers for unix signals.
     */
    protected function bind()
    {
        pcntl_signal(SIGTERM, array($this, 'shutdown'));
        pcntl_signal(SIGQUIT, array($this, 'shutdown'));
        pcntl_signal(SIGINT,  array($this, 'shutdown'));
    }
}
