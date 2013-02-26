<?php

namespace Raekke;

use Raekke\Message\MessageInterface;

/**
 * Responsible for distributing a message to the correct queue.
 *
 * @package Raekke
 */
interface ProducerInterface
{
    /**
     * @param MessageInterface $message
     */
    public function produce(MessageInterface $message);
}
