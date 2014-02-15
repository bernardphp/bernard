<?php

namespace Bernard;

/**
 * @package Bernard
 */
class Producer
{
    protected $queues;
    protected $dispatcher;

    /**
     * @param QueueFactory    $queues
     * @param EventDispatcher $dispatcher
     */
    public function __construct(QueueFactory $queues, EventDispatcher $dispatcher)
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

        $this->dispatcher->emit('bernard.produce', array($envelope, $queue));
    }
}
