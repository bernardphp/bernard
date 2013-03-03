<?php

namespace Raekke\QueueFactory;

/**
 * Knows how to create queues and retrieve them from the used connection.
 * Every queue it creates is saved locally.
 *
 * @package Raekke
 */
interface QueueFactoryInterface extends \Countable
{
    /**
     * @param  string             $queueName
     * @return Raekke\Queue\Queue
     */
    public function create($queueName);

    /**
     * @return array
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
