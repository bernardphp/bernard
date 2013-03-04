<?php

namespace Raekke;

use Raekke\Message;

/**
 * Responsible for distributing a message to the correct queue.
 *
 * @package Raekke
 */
interface ProducerInterface
{
    /**
     * @param Message $message
     */
    public function produce(Message $message);
}
