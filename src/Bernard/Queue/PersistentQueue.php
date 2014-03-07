<?php

namespace Bernard\Queue;

use SplObjectStorage;
use Bernard\Driver;
use Bernard\Envelope;
use Bernard\Encoder;

/**
 * @package Bernard
 */
class PersistentQueue extends AbstractQueue
{
    protected $driver;
    protected $encoder;
    protected $receipts;

    /**
     * @param string  $name
     * @param Driver  $driver
     * @param Encoder $encoder
     */
    public function __construct($name, Driver $driver, Encoder $encoder)
    {
        parent::__construct($name);

        $this->driver   = $driver;
        $this->encoder  = $encoder;
        $this->receipts = array();

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

        $this->driver->pushMessage($this->name, $this->encoder->encode($envelope));
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

        list($encoded, $receipt) = $this->driver->popMessage($this->name);

        if (!$encoded) {
            return;
        }

        if ($encoded) {
            $envelope = $this->encoder->decode($encoded);

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

        return array_map(array($this->encoder, 'decode'), $messages);
    }
}
