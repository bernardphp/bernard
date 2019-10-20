<?php

namespace Bernard;

use Bernard\Event\EnvelopeEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     */
    public function produce(Message $message, $queueName = null)
    {
        $queueName = $queueName ?: Util::guessQueue($message);

        $queue = $this->queues->create($queueName);
        $queue->enqueue($envelope = new Envelope($message));

        $this->dispatch(BernardEvents::PRODUCE, new EnvelopeEvent($envelope, $queue));
    }

    private function dispatch($eventName, EnvelopeEvent $event)
    {
        $this->dispatcher instanceof \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
           ? $this->dispatcher->dispatch($event, $eventName)
           : $this->dispatcher->dispatch($eventName, $event);
    }
}
