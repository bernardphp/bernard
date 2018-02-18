<?php

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
     * @param Envelope $envelope
     *
     * @return Receiver
     *
     * @throws ReceiverNotFoundException
     */
    public function map(Envelope $envelope);
}
