<?php

declare(strict_types=1);

namespace Bernard\Queue;

use Bernard\Driver;
use Bernard\Envelope;
use Bernard\Serializer;
use SplObjectStorage;

class PersistentQueue extends AbstractQueue
{
    protected Driver $driver;
    protected Serializer $serializer;
    protected SplObjectStorage $receipts;
    
    public function __construct(string $name, Driver $driver, Serializer $serializer)
    {
        parent::__construct($name);

        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->receipts = new SplObjectStorage();

        $this->register();
    }

    /**
     * Register with the driver.
     */
    public function register(): void
    {
        $this->errorIfClosed();

        $this->driver->createQueue($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function count() : int
    {
        $this->errorIfClosed();

        return $this->driver->countMessages($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        parent::close();

        $this->driver->removeQueue($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(Envelope $envelope): void
    {
        $this->errorIfClosed();

        $this->driver->pushMessage($this->name, $this->serializer->serialize($envelope));
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(Envelope $envelope): void
    {
        $this->errorIfClosed();

        if ($this->receipts->contains($envelope)) {
            $this->driver->acknowledgeMessage($this->name, $this->receipts[$envelope]);

            $this->receipts->detach($envelope);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param int $duration number of seconds to keep polling for messages
     */
    public function dequeue($duration = 5)
    {
        $this->errorIfClosed();

        $driverMessage = $this->driver->popMessage($this->name, $duration);

        if ($driverMessage) {
            $envelope = $this->serializer->unserialize($driverMessage->message);

            $this->receipts->attach($envelope, $driverMessage->receipt);

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

        return array_map([$this->serializer, 'unserialize'], $messages);
    }
}
