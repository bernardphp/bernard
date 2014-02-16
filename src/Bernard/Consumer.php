<?php

namespace Bernard;

use Bernard\Middleware\MiddlewareBuilder;

/**
 * @package Consumer
 */
class Consumer implements Middleware
{
    protected $router;
    protected $middleware;

    /**
     * @param Router            $router
     * @param MiddlewareBuilder $middleware
     */
    public function __construct(Router $router, MiddlewareBuilder $middleware)
    {
        $this->router = $router;
        $this->middleware = $middleware;
    }

    /**
     * Starts an infinite loop calling Consumer::consume();
     * This should not be used unless in very extreme circumstances.
     *
     * @param Queue $queue
     */
    public function run(Queue $queue)
    {
        while ($this->consume($queue)) {
            // NO op
        }
    }

    /**
     * Returns true do indicate it should be run again or false to indicate
     * it should not be run again.
     *
     * Handles the whole dispatching to receivers and so on.
     *
     * @param  Queue   $queue
     * @return boolean
     */
    public function consume(Queue $queue)
    {
        if (!$envelope = $queue->dequeue()) {
            return true;
        }

        $this->invoke($envelope, $queue);

        return true;
    }

    /**
     * @param Envelope $envelope
     * @param Queue    $queue
     */
    public function invoke(Envelope $envelope, Queue $queue)
    {
        try {
            $middleware = $this->middleware->build($this);
            $middleware->call($envelope, $queue);
        } catch (\Exception $e) {
            // Make sure the exception is not interfering.
            // Previously failing jobs handling have been moved to a middleware.
        }
    }

    /**
     * {@inheritDoc}
     */
    public function call(Envelope $envelope, Queue $queue)
    {
        // for 5.3 support where a function name is not a callable
        call_user_func($this->router->map($envelope), $envelope->getMessage());

        // We successfully processed the message.
        $queue->acknowledge($envelope);
    }
}
