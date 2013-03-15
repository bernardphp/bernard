<?php

namespace Bernard\Pimple;

use Pimple;
use Bernard\Message;
use Bernard\ServiceResolver;

/**
 * @package Bernard
 */
class PimpleAwareResolver implements ServiceResolver
{
    protected $services = array();
    protected $container;

    /**
     * @param Pimple $container
     */
    public function __construct(Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function register($name, $service)
    {
        $this->services[$name] = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Message $message)
    {
        if (!isset($this->services[$message->getName()])) {
            throw new \InvalidArgumentException('No service registered for message "' . $message->getName() . '".');
        }

        return $this->container[$this->services[$message->getName()]];
    }
}
