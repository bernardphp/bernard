<?php

namespace Bernard\Queue;

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
     * @throws InvalidOperationException
     */
    protected function errorIfClosed()
    {
        if ($this->closed) {
            throw new InvalidOperationException(sprintf('Queue "%s" is closed.', $this->name));
        }
    }
}
