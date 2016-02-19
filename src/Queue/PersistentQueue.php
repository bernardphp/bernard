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
    protected $options;

    /**
     * @param string     $name
     * @param Driver     $driver
     * @param Serializer $serializer
     * @param array      $options
     */
    public function __construct($name, Driver $driver, Serializer $serializer, array $options = [])
    {
        parent::__construct($name);

        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->receipts = new \SplObjectStorage();
        $this->options = $options;

        $this->register();
    }

    /**
     * Register with the driver
     */
    public function register()
    {
        $this->errorIfClosed();

        $this->driver->createQueue($this->name, $this->options);
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
    public function enqueue(Envelope $envelope, array $options = [])
    {
        $this->errorIfClosed();

        $this->driver->pushMessage($this->name, $this->serializer->serialize($envelope), $options);
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
}
