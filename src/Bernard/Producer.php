<?php

namespace Bernard;

use Bernard\Message;
use Bernard\Message\Envelope;
use Bernard\QueueFactory;
use Bernard\EventDispatcher\MessageEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @package Bernard
 */
class Producer
{
    protected $factory;
    protected $dispatcher;

    /**
     * @param QueueFactory $factory
     */
    public function __construct(QueueFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Set the event dispatcher to dispatch PRODUCE event.
     *
     * @param  EventDispatcherInterface $dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function produce(Message $message)
    {
        if (null !== $this->dispatcher) {
            $this->dispatcher->dispatch(BernardEvents::PRODUCE, new MessageEvent($message));
        }

        $queue = $this->factory->create($message->getQueue());
        $queue->enqueue(new Envelope($message));
    }
}
