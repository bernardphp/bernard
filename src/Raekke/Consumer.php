<?php

namespace Raekke;

use Raekke\Queue\Queue;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @package Consumer
 */
class Consumer implements ConsumerInterface
{
    protected $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function consume(Queue $queue)
    {
    }
}
