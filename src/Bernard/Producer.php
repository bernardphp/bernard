<?php

namespace Bernard;

use Bernard\Event\EnvelopeEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @package Bernard
 */
class Producer
{
    protected $queues;
    protected $dispatcher;

    /**
     * @param QueueFactory    $queues
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(QueueFactory $queues, EventDispatcherInterface $dispatcher)
    {
        $this->queues = $queues;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param Message     $message
     * @param string|null $queueName
     */
    public function produce(Message $message, $queueName = null)
    {
        $queueName = $queueName ?: bernard_guess_queue($message);

        $queue = $this->queues->create($queueName);
        $queue->enqueue($envelope = new Envelope($message));

        $this->dispatcher->dispatch('bernard.produce', new EnvelopeEvent($envelope, $queue));
    }
}
