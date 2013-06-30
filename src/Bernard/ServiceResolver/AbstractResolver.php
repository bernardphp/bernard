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

        return $this->getInvocator($object, $envelope);
    }

    abstract protected function getService(Envelope $envelope);

    /**
     * @return Invocator
     */
    protected function getInvocator($object, Envelope $envelope)
    {
        return new Invocator($object, $envelope);
    }
}
