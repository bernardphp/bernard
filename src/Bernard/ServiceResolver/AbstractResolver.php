<?php

namespace Bernard\ServiceResolver;

use Bernard\Envelope;

/**
 * @package Bernard
 */
abstract class AbstractResolver implements \Bernard\ServiceResolver
{
    protected $services;

    /**
     * @param array $services
     */
    public function __construct(array $services = array())
    {
        foreach ($services as $name => $service) {
            $this->register($name, $service);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Envelope $envelope)
    {
        if (!$service = $this->getService($envelope)) {
            throw new \InvalidArgumentException('No service registered for envelope "' . $envelope->getName() . '".');
        }

        if (!is_callable($service)) {
            $service = array($service, $this->getMethodName($envelope));
        }

        return $service;
    }

    /**
     * @param  Envelope        $envelope
     * @return object|callable
     */
    abstract protected function getService(Envelope $envelope);

    /**
     * @param  Envelope $envelope
     * @return string
     */
    protected function getMethodName(Envelope $envelope)
    {
        return lcfirst($envelope->getName());
    }
}
