<?php

namespace Bernard;

/**
 * @package Bernard
 */
interface Queue extends \Countable
{
    /**
     * @param Envelope $envelope
     */
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
     *
     * @param Envelope $envelope
     */
    public function acknowledge(Envelope $envelope);

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param array $options
     */
    public function setOptions(array $options);
    
    /**
     * Return the queue textual representation, normally this will be name (not the internal key)
     *
     * @return string
     */
    public function __toString();
}
