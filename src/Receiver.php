<?php

namespace Bernard;

/**
 * Receiver is the target of a message.
 */
interface Receiver
{
    /**
     * Receives and handles a message.
     *
     * @param Message $message
     */
    public function receive(Message $message);
}
