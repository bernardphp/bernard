<?php

namespace Bernard\Queue;

use SplObjectStorage;
use Bernard\Driver;
use Bernard\Message\Envelope;
use Bernard\Serializer;

/**
 * @package Bernard
 */
class PersistentQueue extends AbstractQueue
{
    protected $connection;
    protected $serializer;
    protected $receipts;

    /**
     * @param string     $name
     * @param Driver     $connection
     * @param Serializer $serializer
     */
    public function __construct($name, Driver $connection, Serializer $serializer)
    {
        parent::__construct($name);

        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->receipts   = new SplObjectStorage;

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
    public function acknowledge(Envelope $envelope)
    {
        $this->errorIfClosed();

        if (isset($this->receipts[$envelope])) {
            $this->connection->acknowledgeMessage($this->name, $this->receipts[$envelope]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function dequeue()
    {
        $this->errorIfClosed();

        list($serialized, $receipt) = $this->connection->popMessage($this->name);

        if ($serialized) {
            $envelope = $this->serializer->deserialize($serialized);

            $this->receipts->attach($envelope, $receipt);

            return $envelope;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function peek($index = 0, $limit = 20)
    {
        $this->errorIfClosed();

        $messages = $this->connection->peekQueue($this->name, $index, $limit);

        return array_map(array($this->serializer, 'deserialize'), $messages);
    }
}
