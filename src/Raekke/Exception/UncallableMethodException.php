<?php

namespace Raekke\Exception;

class UncallableMethodException extends \RuntimeException
{
    public function __construct($class, $method)
    {
        parent::__construct(sprintf('Unable to call "%s" on "%s".', $method, $class));
    }
}
