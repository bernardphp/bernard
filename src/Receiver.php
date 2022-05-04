<?php

declare(strict_types=1);

namespace Bernard;

/**
 * Receiver is the target of a message.
 */
interface Receiver
{
    /**
     * Receives and handles a message.
     */
    public function receive(Message $message);
}
