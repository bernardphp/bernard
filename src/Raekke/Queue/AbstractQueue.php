<?php

namespace Raekke\Queue;

use Raekke\Exception\InvalidOperationException;

abstract class AbstractQueue implements QueueInterface
{
    protected $closed;
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function close()
    {
        $this->closed = true;
    }

    public function getName()
    {
        return $this->name;
    }

    protected function errorIfClosed()
    {
        if ($this->closed) {
            throw new InvalidOperationException(sprintf('Queue "%s" is closed.', $this->getName()));
        }
    }
}
