<?php

declare(strict_types=1);

namespace Bernard\Router;

use Bernard\Envelope;
use Bernard\Receiver;

/**
 * SimpleReceiverResolver supports various receiver inputs, like classes objects and callables.
 */
class SimpleReceiverResolver implements ReceiverResolver
{
    /**
     * {@inheritdoc}
     */
    public function accepts($receiver)
    {
        return \is_callable($receiver) || \is_object($receiver) || class_exists($receiver);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($receiver, Envelope $envelope)
    {
        if (null === $receiver) {
            return null;
        }

        if ($receiver instanceof Receiver) {
            return $receiver;
        }

        if (\is_callable($receiver) == false) {
            $receiver = [$receiver, lcfirst($envelope->getName())];
        }

        // Receiver is still not a callable which means it's not a valid receiver.
        if (\is_callable($receiver) == false) {
            return null;
        }

        return new Receiver\CallableReceiver($receiver);
    }
}
