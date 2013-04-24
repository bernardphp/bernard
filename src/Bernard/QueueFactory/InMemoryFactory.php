<?php

namespace Bernard\QueueFactory;

use Bernard\Queue\InMemoryQueue;

/**
 * This is an in memory queue factory. It creates SplQueue objects for the
 * queue. This also means it is not possible to introspect with Juno
 *
 * @package Bernard
 */
class InMemoryFactory implements \Bernard\QueueFactory
{
    /**
     * @var InMemoryQueue[]
     */
    protected $queues;

    /**
     * @param  string               $queueName
     * @return InMemoryQueueFactory
     */
    public function create($queueName)
    {
        if (!$this->exists($queueName)) {
            $this->queues[$queueName] = new InMemoryQueue($queueName);
        }

        return $this->queues[$queueName];
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return $this->queues;
    }

    /**
     * @return integer
     */
    public function count()
    {
        return count($this->queues);
    }

    /**
     * {@inheritDoc}
     */
    public function exists($queueName)
    {
        return isset($this->queues[$queueName]);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($queueName)
    {
        if ($this->exists($queueName)) {
            $this->queues[$queueName]->close();

            unset($this->queues[$queueName]);
        }
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
