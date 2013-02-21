<?php

namespace Raekke\Exception;

class MissingServiceException extends \RuntimeException
{
    public function __construct($messageName)
    {
        parent::__construct(sprintf('No service found for message "%s".', $messageName));
    }
}
