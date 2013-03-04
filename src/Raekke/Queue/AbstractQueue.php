<?php

namespace Raekke\Queue;

use Raekke\Exception\InvalidOperationException;

/**
 * @package Raekke
 */
abstract class AbstractQueue implements \Raekke\Queue
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
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
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
