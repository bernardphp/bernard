<?php

namespace Bernard;

/**
 * Consumes messages from a queue by dequeing messages
 * one at a time and delegating them to the correct service object.
 *
 * @package Bernard
 */
interface ConsumerInterface
{
    /**
     * @param Queue $queue
     * @param Queue $failed
     * @param array $options
     */
    public function consume(Queue $queue, Queue $failed = null, array $options = array());

    /**
     * Return the identification of the consumer. Like what hostname it got
     * what queue(s) it is working.
     *
     * @return string
     */
    public function __toString();
}
