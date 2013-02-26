<?php

namespace Raekke;

use Raekke\Message\MessageInterface;
use Raekke\Message\MessageWrapper;

/**
 * @package Raekke
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
     * Wraps the given message with MessageWrapper and sends
     * it to the queue by its `getQueue` method.
     *
     * @param MessageInterface $message
     */
    public function produce(MessageInterface $message)
    {
        $queue = $this->factory->create($message->getQueue());
        $queue->enqueue(new MessageWrapper($message));
    }
}
