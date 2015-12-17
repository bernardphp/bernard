<?php

namespace Bernard\QueueFactory;

use Bernard\Driver;
use Bernard\Queue\PersistentQueue;
use Bernard\Serializer;

/**
 * Knows how to create queues and retrieve them from the used driver.
 * Every queue it creates is saved locally.
 *
 * @package Bernard
 */
class PersistentFactory implements \Bernard\QueueFactory
{
    protected $queues;
    protected $driver;
    protected $serializer;

    /**
     * @param Driver     $driver
     * @param Serializer $serializer
     */
    public function __construct(Driver $driver, Serializer $serializer)
    {
        $this->queues = [];
        $this->driver = $driver;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function create($queueName)
    {
        if (isset($this->queues[$queueName])) {
            return $this->queues[$queueName];
        }

        $queue = new PersistentQueue($queueName, $this->driver, $this->serializer);

        return $this->queues[$queueName] = $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        // Calls $this->create on every name returned from the driver
        array_map([$this, 'create'], $this->driver->listQueues());

        return $this->queues;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($queueName)
    {
        return isset($this->queues[$queueName]) ?: in_array($queueName, $this->driver->listQueues());
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->driver->listQueues());
    }

    /**
     * {@inheritdoc}
     */
    public function remove($queueName)
    {
        if (!$this->exists($queueName)) {
            return;
        }

        $this->create($queueName)->close();

        unset($this->queues[$queueName]);
    }
}
