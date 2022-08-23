<?php

declare(strict_types=1);

namespace Bernard;

use Bernard\Exception\ReceiverNotFoundException;

/**
 * Router is responsible for routing a message to it's receiver.
 */
interface Router
{
    /**
     * Returns the right Receiver based on the Envelope.
     *
     * @return Receiver
     *
     * @throws ReceiverNotFoundException
     */
    public function route(Envelope $envelope);
}
