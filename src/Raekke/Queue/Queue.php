<?php

namespace Raekke\Queue;

use Raekke\Connection;
use Raekke\Message\Envelope;
use Raekke\Serializer\SerializerInterface;

/**
 * @package Raekke
 */
class Queue extends AbstractQueue
{
    protected $connection;
    protected $serializer;

    public function __construct(
        $name,
        Connection $connection,
        SerializerInterface $serializer
    ) {
        parent::__construct($name);

        $this->connection = $connection;
        $this->serializer = $serializer;

        $this->register();
    }

    public function register()
    {
        $this->errorIfClosed();

        $this->connection->insert('queues', $this->name);
    }

    public function count()
    {
        $this->errorIfClosed();

        return $this->connection->count($this->getKey());
    }

    public function enqueue(Envelope $envelope)
    {
        $this->errorIfClosed();

        $this->connection->push($this->getKey(), $this->serializer->serialize($envelope));
    }

    public function dequeue()
    {
        $this->errorIfClosed();

        if ($message = $this->connection->pop($this->getKey())) {
            return $this->serializer->deserialize($message);
        }

        return null;
    }

    public function slice($index, $length)
    {
        $this->errorIfClosed();

        return array_map(array($this->serializer, 'deserialize'), $this->connection->slice($this->getKey(), $index, $length));
    }

    public function getKey()
    {
        return 'queue:' . $this->name;
    }
}
