<?php

namespace Bernard\Symfony;

use Bernard\Message\Envelope;
use Bernard\ServiceResolver\Invocator;
use Symfony\Component\DependencyInjection\Container;

/**
 * @package Bernard
 */
class ContainerAwareResolver implements \Bernard\ServiceResolver
{
    protected $services = array();
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
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

        return new Invocator($this->container->get($this->services[$envelope->getName()]), $envelope);
    }
}
