<?php

namespace Bernard;

use Bernard\Message\Envelope;

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
     * @return string
     */
    public function getName();

    /**
     * Closes the queue, a closed queue should not be able to perform
     * actions.
     */
    public function close();

    /**
     * @param  integer $index
     * @param  integer $length
     * @return array
     */
    public function slice($index, $length);
}
