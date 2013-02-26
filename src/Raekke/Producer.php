<?php

namespace Raekke;

use Raekke\Message\MessageInterface;
use Raekke\Message\MessageWrapper;

/**
 * @package Raekke
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
    public function produce(MessageInterface $message)
    {
        $queue = $this->factory->create($message->getQueue());
        $queue->enqueue(new MessageWrapper($message));
    }
}
