<?php

namespace Raekke;

use Raekke\QueueFactory;
use Raekke\Message\MessageInterface;
use Raekke\Message\MessageWrapper;

/**
 * @package Raekke
 */
class MessagePublisher
{
    protected $queues;

    /**
     * @param QueueFactory $queues
     */
    public function __construct(QueueFactory $queues)
    {
        $this->queues = $queues;
    }

    /**
     * @param MessageInterface $message
     */
    public function publish(MessageInterface $message)
    {
        $message = new MessageWrapper($message);
        $this->queues->create($message->getMessage()->getQueue())->enqueue($message);
    }
}
