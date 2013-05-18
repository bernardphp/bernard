<?php

namespace Bernard;

use Bernard\Broker;
use Bernard\Message;
use Bernard\Message\Envelope;

/**
 * @package Bernard
 */
class Producer
{
    protected $broker;

    /**
     * @param Broker $broker
     */
    public function __construct(Broker $broker)
    {
        $this->broker = $broker;
    }

    /**
     * {@inheritDoc}
     */
    public function produce(Message $message)
    {
        $queue = $this->broker->create($message->getQueue());
        $queue->enqueue(new Envelope($message));
    }
}
