<?php

namespace Bernard\QueueFactory;

use Bernard\Connection;
use Bernard\Queue\PersistentQueue;
use Bernard\Serializer;

/**
 * Knows how to create queues and retrieve them from the used connection.
 * Every queue it creates is saved locally.
 *
 * @package Bernard
 */
class PersistentFactory implements \Bernard\QueueFactory
{
    /**
     * @var Queue[]
     */
    protected $queues;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Serializer
     */
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function all()
    {
        // Calls $this->create on every name returned from the connection
        array_map(array($this, 'create'), $this->connection->all('queues'));

        return $this->queues;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function remove($queueName)
    {
        if (!$this->exists($queueName)) {
            return;
        }

        $this->create($queueName)->close();

        unset($this->queues[$queueName]);
    }

    /**
     * {@inheritDoc}
     */
    public function get($queueName)
    {
        if (!$this->exists($queueName)) {
            throw new \InvalidArgumentException(sprintf('This queue "%s" does not exist.', $queueName));
        }

        return $this->create($queueName);
    }
}
