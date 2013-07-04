<?php

namespace Bernard\ServiceResolver;

use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
abstract class AbstractResolver implements \Bernard\ServiceResolver
{
    /**
     * {@inheritDoc}
     */
    public function resolve(Envelope $envelope)
    {
        if (!$object = $this->getService($envelope)) {
            throw new \InvalidArgumentException('No service registered for envelope "' . $envelope->getName() . '".');
        }

        return $this->getInvoker($object, $envelope);
    }

    abstract protected function getService(Envelope $envelope);

    /**
     * @return Invoker
     */
    protected function getInvoker($object, Envelope $envelope)
    {
        return new Invoker($object, $envelope);
    }
}
