<?php

namespace Bernard\ServiceResolver;

use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
class ObjectResolver extends AbstractResolver
{
    /**
     * {@inheritDoc}
     */
    public function register($name, $service)
    {
        if (!is_object($service) && !class_exists($service)) {
            throw new \InvalidArgumentException('Expected argument either be "class" or "object" got "' . gettype($service) . '".');
        }

        $this->services[$name] = $service;
    }

    /**
     * {@inheritDoc}
     */
    protected function getService(Envelope $envelope)
    {
        $name = $envelope->getName();

        return isset($this->services[$name]) ? $this->services[$name] : null;
    }
}
