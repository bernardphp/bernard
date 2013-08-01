<?php

namespace Bernard\ServiceResolver;

use Bernard\Message\Envelope;

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
        if ($object = $this->getService($envelope)) {
            return array($object, $this->getMethodName($envelope));
        }

        throw new \InvalidArgumentException('No service registered for envelope "' . $envelope->getName() . '".');
    }

    abstract protected function getService(Envelope $envelope);

    /**
     * @param  Envelope $envelope
     * @return string
     */
    protected function getMethodName(Envelope $envelope)
    {
        return 'on' . ucfirst($envelope->getName());
    }
}
