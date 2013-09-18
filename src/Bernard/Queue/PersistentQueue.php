<?php

namespace Bernard\Queue;

use SplObjectStorage;
use Bernard\Driver;
use Bernard\Envelope;
use Bernard\Serializer;

/**
 * @package Bernard
 */
class PersistentQueue extends AbstractQueue
{
    protected $driver;
    protected $serializer;
    protected $receipts;

    /**
     * @param string     $name
     * @param Driver     $driver
     * @param Serializer $serializer
     */
    public function __construct($name, Driver $driver, Serializer $serializer)
    {
        parent::__construct($name);

        $this->driver     = $driver;
        $this->serializer = $serializer;
        $this->receipts   = array();

        $this->register();
    }

    /**
     * Register with the driver
     */
    public function register()
    {
        $this->errorIfClosed();

        $this->driver->createQueue($this->name);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        $this->errorIfClosed();

        return $this->driver->countMessages($this->name);
    }

    public function close()
    {
        parent::close();

        $this->driver->removeQueue($this->name);
    }

    /**
     * {@inheritDoc}
     */
    public function enqueue(Envelope $envelope)
    {
        $this->errorIfClosed();

        $this->driver->pushMessage($this->name, $this->serializer->serialize($envelope));
    }

    /**
     * {@inheritDoc}
     */
    public function acknowledge(Envelope $envelope)
    {
        $this->errorIfClosed();

        if ($receipt = array_search($envelope, $this->receipts, true)) {
            $this->driver->acknowledgeMessage($this->name, $receipt);

            unset($this->receipts[$receipt]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function dequeue()
    {
        $this->errorIfClosed();

        list($serialized, $receipt) = $this->driver->popMessage($this->name);

        if (!$serialized) {
            return;
        }

        if ($serialized) {
            $envelope = $this->serializer->deserialize($serialized);

            return $this->receipts[$receipt] = $envelope;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function peek($index = 0, $limit = 20)
    {
        $this->errorIfClosed();

        $messages = $this->driver->peekQueue($this->name, $index, $limit);

        return array_map(array($this->serializer, 'deserialize'), $messages);
    }
}
