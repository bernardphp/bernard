<?php

namespace Bernard\Event;

use Bernard\Consumer;

/**
 * @package Bernard
 */
class ConsumerCycleEvent extends \Symfony\Component\EventDispatcher\Event
{
    protected $consumer;

    /**
     * @param Envelope $envelope
     */
    public function __construct(Consumer $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * Stops the Consumer on the next cycle
     */
    public function shutdown()
    {
        $this->consumer->shutdown();
    }
}
