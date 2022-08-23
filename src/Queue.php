<?php

declare(strict_types=1);

namespace Bernard;

interface Queue extends \Countable
{
    public function enqueue(Envelope $envelope);

    /**
     * @return Envelope
     */
    public function dequeue();

    /**
     * Closes the queue, a closed queue should not be able to perform
     * actions.
     */
    public function close();

    /**
     * @param int $index
     * @param int $limit
     *
     * @return array
     */
    public function peek($index = 0, $limit = 20);

    /**
     * SQS requires that a message will be acknowledged or it will be moved back
     * into the queue.
     */
    public function acknowledge(Envelope $envelope);

    /**
     * Return the queue textual representation, normally this will be name (not the internal key).
     *
     * @return string
     */
    public function __toString();
}
