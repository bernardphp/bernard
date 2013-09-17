<?php

namespace Bernard\Symfony;

use Bernard\Envelope;
use Symfony\Component\DependencyInjection\Container;

/**
 * @package Bernard
 */
class ContainerAwareResolver extends \Bernard\ServiceResolver\AbstractResolver
{
    protected $container;

    /**
     * @param Container $container
     * @param array     $services
     */
    public function __construct(Container $container, array $services = array())
    {
        $this->container = $container;

        parent::__construct($services);
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
    protected function getService(Envelope $envelope)
    {
        $name = $envelope->getName();

        if (!isset($this->services[$name])) {
            return;
        }

        return $this->container->get($this->services[$name]);
    }
}
