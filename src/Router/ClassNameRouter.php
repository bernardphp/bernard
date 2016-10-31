<?php

namespace Bernard\Router;

use Bernard\Envelope;
use Bernard\Exception\ReceiverNotFoundException;

/**
 * @package Bernard
 */
class ClassNameRouter extends SimpleRouter
{
    /**
     * {@inheritdoc}
     */
    public function map(Envelope $envelope)
    {
        $receiver = $this->get($envelope->getClass());

        if (!is_callable($receiver)) {
            throw new ReceiverNotFoundException(sprintf('No receiver found for class "%s".', $envelope->getClass()));
        }

        return $receiver;
    }

    /**
     * {@inheritdoc}
     */
    protected function get($name)
    {
        foreach ($this->receivers as $key => $receiver) {
            if (is_a($name, $key, true)) {
                return $receiver;
            }
        }

        return null;
    }
}
