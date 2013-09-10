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
        switch (true) {
            case is_object($service):
            case class_exists($service):
            case is_callable($service):
                break;
            default:
                throw new \InvalidArgumentException('Expected one of [callable, class, object] but got "' . gettype($service) . '".');
        }

        $this->services[$name] = $service;
    }

    /**
     * {@inheritDoc}
     */
    protected function getService(Envelope $envelope)
    {
        $name = $envelope->getName();

        if (isset($this->services[$name])) {
            return $this->services[$name];
        }
    }
}
