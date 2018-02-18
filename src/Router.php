<?php

namespace Bernard;

use Bernard\Exception\ReceiverNotFoundException;

interface Router
{
    /**
     * Returns the right Receiver (callable) based on the Envelope.
     *
     * @param Envelope $envelope
     *
     * @return callable
     *
     * @throws ReceiverNotFoundException
     */
    public function map(Envelope $envelope);
}
