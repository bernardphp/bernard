<?php

namespace Raekke\Exception;

/**
 * @package Raekke
 */
class QueueClosedException extends \RuntimeException
{
    public function __construct($name)
    {
        parent::__construct(sprintf('The Queue "%s" is closed.', $name));
    }
}
