<?php

namespace Bernard;

/**
 * Knows how to create queues and retrieve them from the used connection.
 * Every queue it creates is saved locally.
 *
 * @package Bernard
 */
interface QueueFactory extends \Countable
{
    /**
     * @param  string $queueName
     * @return Queue
     */
    public function create($queueName);

    /**
     * @return Queue[]
     */
    public function all();

    /**
     * @param  string  $queueName
     * @return boolean
     */
    public function exists($queueName);

    /**
     * @param string $queueName
     */
    public function remove($queueName);
}
