<?php

namespace Raekke;

use Raekke\QueueManager;
use Raekke\Message\MessageInterface;

/**
 * @package Raekke
 */
class MessagePublisher
{
    protected $queues;

    /**
     * @param QueueManager $queues
     */
    public function __construct(QueueManager $queues)
    {
        $this->queues = $queues;
    }

    /**
     * @param MessageInterface $message
     */
    public function send(MessageInterface $message)
    {
        $this->queues->get($message->getQueue())->push($message);
    }
}
