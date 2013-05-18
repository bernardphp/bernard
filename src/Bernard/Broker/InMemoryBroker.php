<?php

namespace Bernard\Broker;

use Bernard\Queue\InMemoryQueue;

/**
 * This is an in memory broker. It creates SplQueue objects for the
 * queue. This also means it is not possible to introspect unless it is done
 * in the same request
 *
 * @package Bernard
 */
class InMemoryBroker implements \Bernard\Broker
{
    protected $queues;

    /**
     * @param  string        $queueName
     * @return InMemoryQueue
     */
    public function create($queueName)
    {
        if (!$this->exists($queueName)) {
            $this->queues[$queueName] = new InMemoryQueue($queueName);
        }

        return $this->queues[$queueName];
    }

    /**
     * @return InMemoryQueue[]
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
     * @param  string  $queueName
     * @return boolean
     */
    public function exists($queueName)
    {
        return isset($this->queues[$queueName]);
    }

    /**
     * @param string $queueName
     */
    public function remove($queueName)
    {
        if ($this->exists($queueName)) {
            $this->queues[$queueName]->close();

            unset($this->queues[$queueName]);
        }
    }
}
