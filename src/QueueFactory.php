<?php

declare(strict_types=1);

namespace Bernard;

/**
 * Knows how to create queues and retrieve them from the used connection.
 * Every queue it creates is saved locally.
 */
interface QueueFactory extends \Countable
{
    /**
     * @param string $queueName
     *
     * @return Queue
     */
    public function create($queueName);

    /**
     * @return Queue[]
     */
    public function all();

    /**
     * @param string $queueName
     *
     * @return bool
     */
    public function exists($queueName);

    /**
     * @param string $queueName
     */
    public function remove($queueName);
}
