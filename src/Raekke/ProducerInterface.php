<?php

namespace Raekke;

/**
 * Responsible for distributing a message to the correct queue.
 *
 * @package Raekke
 */
interface Producer
{
    /**
     * @param MessageInterface $message
     */
    public function produce(MessageInterface $message);
}
