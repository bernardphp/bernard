<?php

namespace Raekke;

use Raekke\Message\MessageInterface;
use Raekke\Queue\Queue;
use Raekke\Serializer\Serializer;
use Raekke\Util\ArrayCollection;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Contains a collection of queues.
 *
 * @package Raekke
 */
class QueueManager implements \IteratorAggregate, \ArrayAccess, \Countable
{
    protected $queues;
    protected $connection;
    protected $serializer;

    public function __construct(
        Connection $connection,
        Serializer $serializer = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        $this->queues     = new ArrayCollection;
        $this->connection = $connection;
        $this->serializer = $serializer ?: new Serializer;
        $this->dispatcher = $dispatcher ?: new EventDispatcher;

        $this->get('failed');
    }

    public function get($queueName)
    {
        if ($this->queues->containsKey($queueName)) {
            return $this->queues->get($queueName);
        }

        $this->queues->set($queueName, $queue = $this->createQueueObject($queueName));

        return $queue;
    }

    public function all()
    {
        foreach ($this->connection->all('queues') as $queue) {
            $this->get($queue);
        }

        return $this->queues;
    }

    public function has($queueName)
    {
        if ($this->queues->containsKey($queueName)) {
            return true;
        }

        return (boolean) $this->connection->has('queues', $queueName) ? true : false;
    }

    public function count()
    {
        return $this->connection->count('queues');
    }

    public function push(MessageInterface $message)
    {
        $this->get($message->getQueue())->push($message);
    }

    public function remove($queueName)
    {
        if (!$this->has($queueName)) {
            return false;
        }

        $this->get($queueName)->close();

        $this->queues->remove($queueName);

        return true;
    }

    public function getIterator()
    {
        return $this->all()->getIterator();
    }

    public function offsetSet($queueName, $value)
    {
        throw new \BadMethodCallException('"offsetSet" is not supported.');
    }

    public function offsetGet($queueName)
    {
        return $this->get($queueName);
    }

    public function offsetExists($queueName)
    {
        return $this->has($queueName);
    }

    public function offsetUnset($queueName)
    {
        return $this->remove($queueName);
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getSerializer()
    {
        return $this->serializer;
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    protected function createQueueObject($queueName)
    {
        $queue = new Queue($queueName, $this);
        $queue->attach();

        return $queue;
    }
}
