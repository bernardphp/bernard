<?php

namespace Raekke\QueueFactory;

use Raekke\Connection;
use Raekke\Queue\Queue;
use Raekke\Serializer\SerializerInterface;

/**
 * Knows how to create queues and retrieve them from the used connection.
 * Every queue it creates is saved locally.
 *
 * @package Raekke
 */
class QueueFactory implements QueueFactoryInterface
{
    protected $queues;
    protected $connection;
    protected $serializer;

    public function __construct(
        Connection $connection,
        SerializerInterface $serializer
    ) {
        $this->queues     = array();
        $this->connection = $connection;
        $this->serializer = $serializer;
    }

    public function create($queueName)
    {
        if (isset($this->queues[$queueName])) {
            return $this->queues[$queueName];
        }

        $queue = new Queue($queueName, $this->connection, $this->serializer);

        return $this->queues[$queueName] = $queue;
    }

    public function all()
    {
        // Calls $this->create on every name returned from the connection
        array_map(array($this, 'create'), $this->connection->all('queues'));

        return $this->queues;
    }

    public function exists($queueName)
    {
        if (isset($this->queues[$queueName])) {
            return true;
        }

        return $this->connection->contains('queues', $queueName);
    }

    public function count()
    {
        return count($this->connection->all('queues'));
    }

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
