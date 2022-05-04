<?php

declare(strict_types=1);

namespace Bernard\Router;

use Bernard\Envelope;
use Bernard\Receiver;

/**
 * ReceiverResolver is responsible for resolving a receiver from whatever form it is passed to the router.
 */
interface ReceiverResolver
{
    /**
     * Checks whether the receiver can be resolved using this resolver.
     *
     * @param mixed $receiver
     *
     * @return bool
     */
    public function accepts($receiver);

    /**
     * Resolves a receiver or returns null if it cannot be resolved.
     *
     * @param mixed $receiver
     *
     * @return Receiver|null
     */
    public function resolve($receiver, Envelope $envelope);
}
