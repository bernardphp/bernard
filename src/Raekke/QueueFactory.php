<?php

namespace Raekke;

use Raekke\Message\MessageInterface;
use Raekke\Queue\Queue;
use Raekke\Serializer\SerializerInterface;
use Raekke\Util\ArrayCollection;

/**
 * Knows how to create queues and retrieve them from the used connection.
 * Every queue it creates is saved locally.
 *
 * @package Raekke
 */
class QueueFactory implements \Countable
{
    protected $queues;
    protected $connection;
    protected $serializer;

    public function __construct(
        Connection $connection,
        SerializerInterface $serializer
    ) {
        $this->queues     = new ArrayCollection;
        $this->connection = $connection;
        $this->serializer = $serializer;
    }

    public function create($queueName)
    {
        if ($this->queues->containsKey($queueName)) {
            return $this->queues->get($queueName);
        }

        $queue = new Queue($queueName, $this->connection, $this->serializer);
        $this->queues->set($queueName, $queue);

        return $queue;
    }

    public function all()
    {
        // Calls $this->create on every name returned from the connection
        array_map(array($this, 'create'), $this->connection->all('queues'));

        return $this->queues;
    }

    public function exists($queueName)
    {
        if ($this->queues->containsKey($queueName)) {
            return true;
        }

        return (boolean) $this->connection->contains('queues', $queueName);
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

        $this->queues->remove($queueName);

        return true;
    }
}
