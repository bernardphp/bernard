<?php

namespace Raekke\QueueFactory;

use Raekke\Connection;
use Raekke\Queue\PersistentQueue;
use Raekke\Serializer;

/**
 * Knows how to create queues and retrieve them from the used connection.
 * Every queue it creates is saved locally.
 *
 * @package Raekke
 */
class PersistentFactory implements \Raekke\QueueFactory
{
    protected $queues;
    protected $connection;
    protected $serializer;

    /**
     * @param Connection          $connection
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Connection $connection,
        Serializer $serializer
    ) {
        $this->queues     = array();
        $this->connection = $connection;
        $this->serializer = $serializer;
    }

    /**
     * @param  string $queueName
     * @return Queue
     */
    public function create($queueName)
    {
        if (isset($this->queues[$queueName])) {
            return $this->queues[$queueName];
        }

        $queue = new PersistentQueue($queueName, $this->connection, $this->serializer);

        return $this->queues[$queueName] = $queue;
    }

    /**
     * @return Queue[]
     */
    public function all()
    {
        // Calls $this->create on every name returned from the connection
        array_map(array($this, 'create'), $this->connection->all('queues'));

        return $this->queues;
    }

    /**
     * @param  string  $queueName
     * @return boolean
     */
    public function exists($queueName)
    {
        if (isset($this->queues[$queueName])) {
            return true;
        }

        return $this->connection->contains('queues', $queueName);
    }

    /**
     * @return integer
     */
    public function count()
    {
        return count($this->connection->all('queues'));
    }

    /**
     * @param string $queueName
     */
    public function remove($queueName)
    {
        if (!$this->exists($queueName)) {
            return false;
        }

        $this->create($queueName)->close();

        unset($this->queues[$queueName]);

        return true;
    }
}
