<?php

namespace Bernard\QueueFactory;

use Bernard\Queue\SyncQueue;
use Bernard\QueueFactory;
use Bernard\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Knows how to create queues and retrieve them from the used driver.
 * Every queue it creates is saved locally.
 *
 * @package Bernard
 */
class SyncFactory implements QueueFactory
{
    /**
     * @var array
     */
    protected $queues;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var Router
     */
    protected $router;

    /**
     * SyncFactory constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param Router $router
     */
    public function __construct(EventDispatcherInterface $dispatcher, Router $router)
    {
        $this->queues = [];
        $this->dispatcher = $dispatcher;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function create($queueName)
    {
        if (!$this->exists($queueName)) {
            $this->queues[$queueName] = new SyncQueue($queueName, $this->dispatcher, $this->router);
        }

        return $this->queues[$queueName];
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->queues;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->queues);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($queueName)
    {
        return isset($this->queues[$queueName]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($queueName)
    {
        if ($this->exists($queueName)) {
            $this->queues[$queueName]->close();

            unset($this->queues[$queueName]);
        }
    }
}
