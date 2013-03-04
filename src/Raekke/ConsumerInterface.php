<?php

namespace Raekke;

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
     * @param array $options
     */
    public function consume(Queue $queue, array $options = array());
}
