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
     * @param  Envelope                  $envelope
     * @throws ReceiverNotFoundException
     * @return array
     */
    public function map(Envelope $envelope);
}
