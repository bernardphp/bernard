<?php

namespace Bernard\Queue;

use Bernard\Connection;
use Bernard\Message\Envelope;
use Bernard\Serializer;

/**
 * @package Bernard
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
        Serializer $serializer
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

        $this->connection->createQueue($this->name);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        $this->errorIfClosed();

        return $this->connection->countMessages($this->name);
    }

    public function close()
    {
        parent::close();

        $this->connection->removeQueue($this->name);
    }

    /**
     * {@inheritDoc}
     */
    public function enqueue(Envelope $envelope)
    {
        $this->errorIfClosed();

        $this->connection->pushMessage($this->name, $this->serializer->serialize($envelope));
    }

    /**
     * {@inheritDoc}
     */
    public function dequeue()
    {
        $this->errorIfClosed();

        $message = $this->connection->popMessage($this->name);

        return $message ? $this->serializer->deserialize($message) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function peek($index = 0, $limit = 20)
    {
        $this->errorIfClosed();
        $messages = $this->connection->peekQueue($this->name, $index, $limit);

        return array_map(array($this->serializer, 'deserialize'), $message);
    }
}
