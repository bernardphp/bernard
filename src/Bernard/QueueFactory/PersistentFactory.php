<?php

namespace Bernard\QueueFactory;

use Bernard\Driver;
use Bernard\Queue\PersistentQueue;
use Bernard\Encoder;

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
    protected $encoder;

    /**
     * @param Driver  $driver
     * @param Encoder $encoder
     */
    public function __construct(Driver $driver, Encoder $encoder)
    {
        $this->queues  = array();
        $this->driver  = $driver;
        $this->encoder = $encoder;
    }

    /**
     * {@inheritDoc}
     */
    public function create($queueName)
    {
        if (isset($this->queues[$queueName])) {
            return $this->queues[$queueName];
        }

        $queue = new PersistentQueue($queueName, $this->driver, $this->encoder);

        return $this->queues[$queueName] = $queue;
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        // Calls $this->create on every name returned from the driver
        array_map(array($this, 'create'), $this->driver->listQueues());

        return $this->queues;
    }

    /**
     * {@inheritDoc}
     */
    public function exists($queueName)
    {
        return isset($this->queues[$queueName]) ?: in_array($queueName, $this->driver->listQueues());
    }

    /**
     * @return integer
     */
    public function count()
    {
        return count($this->driver->listQueues());
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
}
