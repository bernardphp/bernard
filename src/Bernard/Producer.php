<?php

namespace Bernard;

use Bernard\Message;
use Bernard\Message\Envelope;
use Bernard\QueueFactory;

/**
 * @package Bernard
 */
class Producer
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
    public function produce(Message $message, $queueName = null)
    {
        $envelope = new Envelope($message);

        $this->factory->create($queueName ?: $message->getQueue())->enqueue($envelope);
    }
}
