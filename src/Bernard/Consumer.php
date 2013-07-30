<?php

namespace Bernard;

use Bernard\Message\Envelope;
use Bernard\ServiceResolver\Invoker;
use Exception;

declare(ticks=1);

/**
 * @package Consumer
 */
class Consumer
{
    protected $services;
    protected $shutdown = false;
    protected $configured = false;
    protected $bound = false;
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
        while ($this->tick($queue, $failed, $options)) {
            // NO op
        }
    }

    /**
     * Returns true do indicate it should be run again or false to indicate
     * it should not be run again.
     *
     * @param  Queue      $queue
     * @param  Queue|null $failed
     * @return boolean
     */
    public function tick(Queue $queue, Queue $failed = null, array $options = array())
    {
        $this->bind();
        $this->configure($options);

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
            $this->invoke($envelope, $queue);
        } catch (Exception $e) {
            $this->fail($envelope, $e, $queue, $failed);
        }

        $queue->acknowledge($envelope);

        return true;
    }

    /**
     * Mark Consumer as shutdown
     */
    public function shutdown()
    {
        $this->shutdown = true;
    }

    /**
     * Until there is a real extension point to doing invoked stuff, this can be used
     * by wrapping the invoke method.
     *
     * @param Envelope $envelope
     * @param Queue $queue
     */
    protected function invoke(Envelope $envelope, Queue $queue)
    {
        $invoker = new Invoker($this->services->resolve($envelope));
        $invoker->invoke($envelope);
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

            $failed = $queue;
        }

        if ($failed) {
            // As we are manually requeuing the envelope we must acknowledge it
            // or it will be duplicated.
            $queue->acknowledge($envelope);
            $failed->enqueue($envelope);
        }
    }

    /**
     * @param array $options
     */
    protected function configure(array $options)
    {
        if ($this->configured) {
            return $this->options;
        }

        $this->options = array_filter($options) + $this->options;
        $this->options['max-runtime'] += microtime(true);
        $this->configured = true;
    }

    /**
     * Setup signal handlers for unix signals.
     */
    protected function bind()
    {
        if ($this->bound) {
            return;
        }

        pcntl_signal(SIGTERM, array($this, 'shutdown'));
        pcntl_signal(SIGQUIT, array($this, 'shutdown'));
        pcntl_signal(SIGINT,  array($this, 'shutdown'));

        $this->bound = true;
    }
}
