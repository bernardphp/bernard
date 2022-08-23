<?php

declare(strict_types=1);

namespace Bernard\QueueFactory;

use Bernard\Queue\InMemoryQueue;

/**
 * This is an in memory queue factory. It creates SplQueue objects for the
 * queue. This also means it is not possible to introspect with Juno.
 */
class InMemoryFactory implements \Bernard\QueueFactory
{
    protected $queues = [];

    /**
     * {@inheritdoc}
     */
    public function create($queueName)
    {
        if (!$this->exists($queueName)) {
            $this->queues[$queueName] = new InMemoryQueue($queueName);
        }

        return $this->queues[$queueName];
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->queues;
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return \count($this->queues);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($queueName)
    {
        return isset($this->queues[$queueName]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($queueName): void
    {
        if ($this->exists($queueName)) {
            $this->queues[$queueName]->close();

            unset($this->queues[$queueName]);
        }
    }
}
