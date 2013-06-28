<?php

namespace Bernard\ServiceResolver;

use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
class ObjectResolver implements \Bernard\ServiceResolver
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
    public function resolve(Envelope $envelope)
    {
        $name = $envelope->getName();

        if (isset($this->services[$name])) {
            return new Invocator($this->services[$name], $envelope);
        }

        throw new \InvalidArgumentException('No service registered for message "' . $name . '".');
    }
}
