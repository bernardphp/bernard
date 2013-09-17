<?php

namespace Bernard;

use Bernard\Middleware\MiddlewareBuilder;
use Bernard\ServiceResolver\Invoker;
use Exception;

declare(ticks=1);

/**
 * @package Consumer
 */
class Consumer
{
    protected $services;
    protected $middleware;
    protected $shutdown = false;
    protected $configured = false;
    protected $options = array(
        'max-runtime' => PHP_INT_MAX,
    );

    /**
     * @param ServiceResolver   $services
     * @param MiddlewareBuilder $middleware
     */
    public function __construct(ServiceResolver $services, MiddlewareBuilder $middleware)
    {
        $this->services = $services;
        $this->middleware = $middleware;
    }

    /**
     * Starts an infinite loop calling Consumer::tick();
     *
     * @param Queue $queue
     * @param array $options
     */
    public function consume(Queue $queue, array $options = array())
    {
        $this->bind();

        while ($this->tick($queue, $options)) {
            // NO op
        }
    }

    /**
     * Returns true do indicate it should be run again or false to indicate
     * it should not be run again.
     *
     * @param  Queue   $queue
     * @param  array   $options
     * @return boolean
     */
    public function tick(Queue $queue, array $options = array())
    {
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

        $this->invoke($envelope, $queue);

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
     * @param Queue    $queue
     */
    public function invoke(Envelope $envelope, Queue $queue)
    {
        $callable = $this->services->resolve($envelope);
        $invoker = $this->middleware->build(new Invoker($callable));

        try {
            $invoker->call($envelope);
        } catch (Exception $e) {
            // Make sure the exception is not interfering.
            // Previously failing jobs handling have been moved to a middleware.
        }

        $queue->acknowledge($envelope);
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
        pcntl_signal(SIGTERM, array($this, 'shutdown'));
        pcntl_signal(SIGQUIT, array($this, 'shutdown'));
        pcntl_signal(SIGINT,  array($this, 'shutdown'));
    }
}
