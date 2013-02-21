<?php

namespace Raekke;

use Raekke\Message\MessageInterface;
use Raekke\Exception\UncallableMethodException;
use Raekke\Exception\MissingServiceException;

/**
 * @package Raekke
 */
class WorkerManager
{
    protected $services;

    public function register($name, $service)
    {
        $this->services[strtolower($name)] = $service;
    }

    public function getService($messageName)
    {
        $messageName = strtolower($messageName);

        if (isset($this->services[$messageName])) {
            return $this->services[$messageName];
        }

        throw new MissingServiceException($messageName);
    }

    public function handle(MessageInterface $message)
    {
        $service = $this->getService($message->getName());
        $method = 'on' . ucfirst($message->getName());

        if (!is_callable(array($service, $method))) {
            throw new UncallableMethodException(get_class($service), $method);
        }

        $service->$method($message);
    }
}
