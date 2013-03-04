<?php

namespace Raekke\Queue;

use Raekke\Connection;
use Raekke\Message\Envelope;
use Raekke\Serializer\SerializerInterface;

/**
 * @package Raekke
 */
class PersistentQueue extends AbstractQueue
{
    protected $connection;
    protected $serializer;

    /**
     * @param string              $name
     * @param Connection          $connection
     * @param SerializerInterface $serializer
     */
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

    /**
     * Register with the connection
     */
    public function register()
    {
        $this->errorIfClosed();

        $this->connection->insert('queues', $this->name);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        $this->errorIfClosed();

        return $this->connection->count($this->getKey());
    }

    /**
     * {@inheritDoc}
     */
    public function enqueue(Envelope $envelope)
    {
        $this->errorIfClosed();

        $this->connection->push($this->getKey(), $this->serializer->serialize($envelope));
    }

    /**
     * {@inheritDoc}
     */
    public function dequeue()
    {
        $this->errorIfClosed();

        if ($message = $this->connection->pop($this->getKey())) {
            return $this->serializer->deserialize($message);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function slice($index, $length)
    {
        $this->errorIfClosed();

        return array_map(array($this->serializer, 'deserialize'), $this->connection->slice($this->getKey(), $index, $length));
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'queue:' . $this->name;
    }
}
