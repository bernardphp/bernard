<?php

namespace Bernard\Router;

use Bernard\Envelope;

/**
 * Uses the message class name as the message name.
 * Allows registering catch-all receivers for message parent types.
 */
final class ClassNameRouter extends ReceiverMapRouter
{
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

    /**
     * {@inheritdoc}
     */
    protected function getName(Envelope $envelope)
    {
        return $envelope->getClass();
    }
}
