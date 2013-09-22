<?php

namespace Bernard;

/**
 * @package Bernard
 */
interface Router
{
    /**
     * Returns the right Receiver (callable) based on the Envelope.
     *
     * @param  Envelope $envelope
     * @throws Exception\ReceiverNotFoundException
     */
    public function map(Envelope $envelope);
}
