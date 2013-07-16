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
        if ($object = $this->getService($envelope)) {
            return array($object, 'on' . ucfirst($envelope->getName()));
        }

        throw new \InvalidArgumentException('No service registered for envelope "' . $envelope->getName() . '".');
    }

    abstract protected function getService(Envelope $envelope);
}
