<?php

namespace Bernard\Pimple;

use Pimple;
use Bernard\Message\Envelope;
use Bernard\ServiceResolver\Invocator;

/**
 * @package Bernard
 */
class PimpleAwareResolver implements \Bernard\ServiceResolver
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
    public function resolve(Envelope $envelope)
    {
        if (!isset($this->services[$envelope->getName()])) {
            throw new \InvalidArgumentException('No service registered for envelope "' . $envelope->getName() . '".');
        }

        return new Invocator($this->container[$this->services[$envelope->getName()]], $envelope);
    }
}
