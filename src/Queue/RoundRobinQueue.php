<?php

namespace Bernard\Queue;

use Bernard\Envelope;
use Bernard\Queue;

/**
 * @package Bernard
 */
class RoundRobinQueue implements Queue
{
    /**
     * @var Queue[]
     */
    protected $queues;

    /**
     * @var bool
     */
    protected $closed;

    /**
     * @var \SplObjectStorage
     */
    protected $envelopes;

    /**
     * @param Queue[] $queues
     */
    public function __construct(array $queues)
    {
        $this->validateQueues($queues);

        $this->queues = $this->indexQueues($queues);
        $this->envelopes = new \SplObjectStorage();
        $this->closed = false;
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(Envelope $envelope)
    {
        $this->verifyEnvelope($envelope);

        $this->queues[$envelope->getName()]->enqueue($envelope);
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        $envelope = null;
        $checked = [];

        while (count($checked) < count($this->queues)) {
            $queue = current($this->queues);
            $envelope = $queue->dequeue();
            if (false === next($this->queues)) {
                reset($this->queues);
            }
            if ($envelope) {
                $this->envelopes->attach($envelope, $queue);
                break;
            } else {
                $checked[] = $queue;
            }
        }

        return $envelope;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->closed) {
            return;
        }

        foreach ($this->queues as $queue) {
            $queue->close();
        }

        $this->closed = true;
    }

    /**
     * {@inheritdoc}
     */
    public function peek($index = 0, $limit = 20)
    {
        $it = new \InfiniteIterator(new \ArrayIterator($this->queues));
        $envelopes = $drained = $indexes = [];
        foreach (array_keys($this->queues) as $name) {
            $indexes[$name] = 0;
        }
        $shift = 0;

        $key = key($this->queues);
        for ($it->rewind(); $it->key() != $key; $it->next()) {
            // noop
        }

        while (count($envelopes) < $limit && count($drained) < $it->count()) {
            $queue = $it->current();
            $name = $it->key();
            if ($peeked = $queue->peek($indexes[$name], 1)) {
                if ($shift < $index) {
                    $shift++;
                    $indexes[$name]++;
                } else {
                    $envelopes[] = array_shift($peeked);
                }
            } else {
                $drained[$name] = true;
            }
            $it->next();
        }

        return $envelopes;
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledge(Envelope $envelope)
    {
        if (!$this->envelopes->contains($envelope)) {
            throw new \DomainException(
                'Unrecognized queue specified: ' . $envelope->getName()
            );
        }

        $queue = $this->envelopes[$envelope];
        $queue->acknowledge($envelope);
        $this->envelopes->detach($envelope);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) current($this->queues);
    }

    /**
     * @return int
     */
    public function count()
    {
        return array_sum(array_map('count', $this->queues));
    }

    /**
     * @param Queue[] $queues
     */
    protected function validateQueues(array $queues)
    {
        if (empty($queues)) {
            throw new \DomainException('$queues cannot be empty');
        }

        $filtered = array_filter(
            $queues,
            function ($queue) {
                return !$queue instanceof Queue;
            }
        );
        if (!empty($filtered)) {
            throw new \DomainException('All elements of $queues must implement Queue');
        }
    }

    /**
     * @param Queue[] $queues
     *
     * @return Queue[]
     */
    protected function indexQueues(array $queues)
    {
        return array_combine(
            array_map(
                function ($queue) {
                    return (string) $queue;
                },
                $queues
            ),
            $queues
        );
    }

    /**
     * @param Envelope $envelope
     */
    protected function verifyEnvelope(Envelope $envelope)
    {
        $queue = $envelope->getName();
        if (isset($this->queues[$queue])) {
            return;
        }
        throw new \DomainException('Unrecognized queue specified: ' . $queue);
    }
}
