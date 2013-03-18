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
     * @return string
     */
    public function getName();
}
