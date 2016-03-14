<?php

namespace Bernard\Queue;

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

        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->receipts = new \SplObjectStorage();

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
     * {@inheritdoc}
     */
    public function count()
    {
        $this->errorIfClosed();

        return $this->driver->countMessages($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        parent::close();

        $this->driver->removeQueue($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(Envelope $envelope)
    {
        $this->errorIfClosed();

        $receipt = $this->driver->pushMessage($this->name, $this->serializer->serialize($envelope));

        if ($receipt) {
            $envelope->setReceipt($receipt);
            $this->receipts->attach($envelope, $receipt);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(Envelope $envelope)
    {
        $this->errorIfClosed();

        if ($this->receipts->contains($envelope)) {
            $this->driver->acknowledgeMessage($this->name, $this->receipts[$envelope]);

            $this->receipts->detach($envelope);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        $this->errorIfClosed();

        list($serialized, $receipt) = $this->driver->popMessage($this->name);

        if ($serialized) {
            $envelope = $this->serializer->unserialize($serialized);

            $envelope->setReceipt($receipt);
            $this->receipts->attach($envelope, $receipt);

            return $envelope;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function peek($index = 0, $limit = 20)
    {
        $this->errorIfClosed();

        $messages = $this->driver->peekQueue($this->name, $index, $limit);

        return array_map(array($this->serializer, 'unserialize'), $messages);
    }

    /**
     * @param Envelope $envelope The envelope.
     * @return mixed
     */
    public function getReceipt(Envelope $envelope)
    {
        if (!$this->receipts->contains($envelope)) {
            return null;
        }

        return $this->receipts->offsetGet($envelope);
    }
}
