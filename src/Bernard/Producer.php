<?php

namespace Bernard;

use Bernard\Message;
use Bernard\Message\Envelope;
use Bernard\QueueFactory;

/**
 * @package Bernard
 */
class Producer implements ProducerInterface
{
    protected $factory;

    /**
     * @param QueueFactory $factory
     */
    public function __construct(QueueFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function produce(Message $message)
    {
        $queue = $this->factory->create($message->getQueue());
        $queue->enqueue(new Envelope($message));
    }
}
