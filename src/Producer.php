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
     * @param QueueFactory             $queues
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
     * @param array       $options
     */
    public function produce(Message $message, $queueName = null, array $options = [])
    {
        $queueName = $queueName ?: Util::guessQueue($message);

        $queue = $this->queues->create($queueName);
        $queue->enqueue($envelope = new Envelope($message), $options);

        $this->dispatcher->dispatch(BernardEvents::PRODUCE, new EnvelopeEvent($envelope, $queue));
    }
}
