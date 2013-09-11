<?php

namespace Bernard\Queue;

use Bernard\Envelope;
use Bernard\Exception\InvalidOperationException;

/**
 * @package Bernard
 */
abstract class AbstractQueue implements \Bernard\Queue
{
    protected $closed;
    protected $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        $this->closed = true;
    }

    /**
     * By Default this is not implemented. For Memory queues it does not make sense.
     *
     * {@inheritDoc}
     */
    public function acknowledge(Envelope $envelope)
    {
        $this->errorIfClosed();
    }

    /**
     * Enqueues the envelope on the queue. This is a shortcut for
     * using it as a middleware.
     *
     * @param Envelope $envelope
     */
    public function call(Envelope $envelope)
    {
        $this->enqueue($envelope);
    }

    /**
     * @throws InvalidOperationException
     */
    protected function errorIfClosed()
    {
        if ($this->closed) {
            throw new InvalidOperationException(sprintf('Queue "%s" is closed.', $this->name));
        }
    }
}
