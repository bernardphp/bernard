<?php

namespace Raekke\ServiceResolver;

use Raekke\Message\MessageInterface;

/**
 * @package Raekke
 */
class ServiceResolver implements ServiceResolverInterface
{
    protected $services = array();

    /**
     * {@inheritDoc}
     */
    public function register($name, $service)
    {
        if (!is_object($service)) {
            throw new \InvalidArgumentException('The given service is not an object.');
        }

        $this->services[$name] = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(MessageInterface $message)
    {
        if (isset($this->services[$message->getName()])) {
            return $this->services[$message->getName()];
        }

        throw new \InvalidArgumentException('No service registered for message "' . $message->getName() . '".');
    }
}
