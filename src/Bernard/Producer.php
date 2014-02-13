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
        if ($message instanceof Batch) {
            foreach ($message->flush() as $envelope) {
                $this->doProduce($envelope, $queueName);
            }

            return;
        }

        $this->doProduce(new Envelope($message), $queueName);
    }

    protected function doProduce(Envelope $envelope, $queueName = null)
    {
        $queueName = $queueName ?: bernard_guess_queue($envelope->getMessage());

        $this->dispatcher->dispatch('bernard.produce', new EnvelopeEvent($envelope, $this->queues->create($queueName)));
    }
}
