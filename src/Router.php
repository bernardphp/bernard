<?php

namespace Bernard;

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
