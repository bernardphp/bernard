<?php

namespace Raekke;

use Raekke\Message\MessageInterface;
use Raekke\Util\ArrayCollection;

/**
 * @package Raekke
 */
class ServiceResolver implements ServiceResolverInterface
{
    protected $services;

    public function __construct()
    {
        $this->services = new ArrayCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function register($name, $service)
    {
        if (!is_object($service)) {
            throw new \InvalidArgumentException('The given service is not an object.');
        }

        $this->services->set($name, $service);
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(MessageInterface $message)
    {
        if ($this->services->containsKey($message->getName())) {
            return $this->services->get($message->getName());
        }

        throw new \InvalidArgumentException('No service registered for message "' . $message->getName() . '".');
    }
}
