<?php

namespace Bernard;

use Bernard\Message;
use Bernard\Message\Envelope;
use Bernard\QueueFactory;
use Psr\Log\LoggerInterface;

/**
 * @package Bernard
 */
class Producer
{
    protected $factory;
    protected $logger;

    /**
     * @param QueueFactory $factory
     * @param LoggerInterface $logger
     */
    public function __construct(QueueFactory $factory, LoggerInterface $logger = null)
    {
        $this->factory = $factory;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function produce(Message $message)
    {
        $queue = $this->factory->create($message->getQueue());
        $queue->enqueue(new Envelope($message));

        if (null !== $this->logger) {
            $this->logger->info('Enqueued message {name} to {queue}', array(
                'name'    => $message->getName(),
                'queue'   => $message->getQueue(),
                'message' => $message,
            ));
        }
    }
}
