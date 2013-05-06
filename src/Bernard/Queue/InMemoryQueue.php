<?php

namespace Bernard\Queue;

use Bernard\Message\Envelope;
use SplQueue;

/**
 * Wrapper around SplQueue
 *
 * @package Bernard
 */
class InMemoryQueue extends AbstractQueue
{
    protected $queue;

    /**
     * {@inheritDoc}
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->queue = new SplQueue;
        $this->queue->setIteratorMode(SplQueue::IT_MODE_DELETE | SplQueue::IT_MODE_FIFO);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        $this->errorIfClosed();

        return $this->queue->count();
    }

    /**
     * {@inheritDoc}
     */
    public function enqueue(Envelope $envelope)
    {
        $this->errorIfClosed();

        $this->queue->enqueue($envelope);
    }

    /**
     * {@inheritDoc}
     */
    public function dequeue()
    {
        $this->errorIfClosed();

        if ($this->count()) {
            return $this->queue->dequeue();
        }

        usleep(350);

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function peek($index = 1, $limit = 20)
    {
        $this->errorIfClosed();

        if (!$this->count()) {
            return array();
        }

        $envelopes = array();
        $queue = clone $this->queue;
        $key = -1;

        while ($envelope = $queue->dequeue()) {
            $key++;

            if ($key < $index) {
                continue;
            }

            $envelopes[] = $envelope;

            if (count($envelopes) >= $length) {
                break;
            }
        }

        return $envelopes;
    }
}
