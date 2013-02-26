<?php

namespace Raekke;

use Raekke\Queue\Queue;

/**
 * Consumes messages from a queue by dequeing messages
 * one at a time and delegating them to the correct service object.
 *
 * @package Raekke
 */
interface ConsumerInterface
{
    /**
     * @param Queue $queue
     */
    public function consume(Queue $queue);
}
