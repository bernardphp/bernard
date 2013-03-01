<?php

namespace Raekke\QueueFactory;

use Raekke\Queue\InMemoryQueue;

/**
 * This is an in memory queue factory. It creates SplQueue objects for the
 * queue. This also means it is not possible to introspect with Juno
 *
 * @package Raekke
 */
class InMemoryQueueFactory implements QueueFactoryInterface
{
    protected $queues;

    public function create($queueName)
    {
        if (!$this->exists($queueName)) {
            $this->queues[$queueName] = new InMemoryQueue($queueName);
        }

        return $this->queues[$queueName];
    }

    public function all()
    {
        return $this->queues;
    }

    public function count()
    {
        return count($this->queues);
    }

    public function exists($queueName)
    {
        return isset($this->queues[$queueName]);
    }

    public function remove($queueName)
    {
        if ($this->exists($queueName)) {
            $this->queues[$queueName]->close();

            unset($this->queues[$queueName]);
        }
    }
}
