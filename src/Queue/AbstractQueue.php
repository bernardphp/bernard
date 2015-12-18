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
     * {@inheritdoc}
     */
    public function close()
    {
        $this->closed = true;
    }

    /**
     * By Default this is not implemented. For Memory queues it does not make sense.
     *
     * {@inheritdoc}
     */
    public function acknowledge(Envelope $envelope)
    {
        $this->errorIfClosed();
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

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->name;
    }
}
