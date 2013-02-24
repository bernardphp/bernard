<?php

namespace Raekke\Queue;

use Raekke\Connection;
use Raekke\Message\MessageWrapper;
use Raekke\Serializer\SerializerInterface;
use Raekke\Util\ArrayCollection;

/**
 * @package Raekke
 */
class Queue implements \Countable
{
    protected $key;
    protected $connection;
    protected $serializer;
    protected $closed = false;

    public function __construct(
        $name,
        Connection $connection,
        SerializerInterface $serializer
    ) {
        $this->name       = $name;
        $this->connection = $connection;
        $this->serializer = $serializer;

        $this->attach();
    }

    public function attach()
    {
        $this->errorIfClosed();

        $this->connection->insert('queues', $this->name);
    }

    public function count()
    {
        $this->errorIfClosed();

        return $this->connection->count($this->getKey());
    }

    public function enqueue(MessageWrapper $message)
    {
        $this->errorIfClosed();

        $this->connection->push($this->getKey(), $this->serializer->serialize($wrapper));
    }

    public function close()
    {
        $this->errorIfClosed();

        $this->closed = true;

        $this->connection->remove('queues', $this->name);
        $this->connection->delete($this->getKey());

        return $this->closed;
    }

    public function slice($index, $length)
    {
        $this->errorIfClosed();

        $messages = $this->connection->slice($this->getKey(), $index, $length);
        $messages = new ArrayCollection($messages);

        $serializer = $this->serializer;

        return $messages->map(function ($payload) use ($serializer) {
            return $serializer->deserialize($payload, false);
        });
    }

    public function isClosed()
    {
        return $this->closed;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getKey()
    {
        return 'queue:' . $this->name;
    }

    protected function errorIfClosed()
    {
        if ($this->closed) {
            throw new \LogicException('The Queue is closed.');
        }
    }
}
