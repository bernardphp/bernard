<?php

namespace Raekke\Queue;

use Raekke\Message\Envelope;
use SplQueue;

/**
 * Wrapper around SplQueue
 *
 * @package Raekke
 */
class InMemoryQueue extends AbstractQueue
{
    protected $queue;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->queue = new SplQueue;
        $this->queue->setIteratorMode(SplQueue::IT_MODE_DELETE);
    }

    public function count()
    {
        $this->errorIfClosed();

        return $this->queue->count();
    }

    public function enqueue(Envelope $envelope)
    {
        $this->errorIfClosed();

        $this->queue->enqueue($envelope);
    }

    public function dequeue()
    {
        $this->errorIfClosed();

        if ($this->count()) {
            return $this->queue->dequeue();
        }

        return null;
    }

    public function slice($index, $length)
    {
        $this->errorIfClosed();

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
