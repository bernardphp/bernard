<?php

namespace Bernard\Queue;

use Bernard\BernardEvents;
use Bernard\Envelope;
use Bernard\Event\EnvelopeEvent;
use Bernard\Event\PingEvent;
use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SyncQueue extends AbstractQueue
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var Router
     */
    protected $router;

    public function __construct($name, EventDispatcherInterface $dispatcher, Router $router)
    {
        parent::__construct($name);
        $this->dispatcher = $dispatcher;
        $this->router = $router;
    }

    /**
     * Copy-pasted from Consumer::invoke() and modified
     * @param Envelope $envelope
     * @throws \Exception
     * @throws \Throwable
     */
    public function enqueue(Envelope $envelope)
    {
        $this->errorIfClosed();

        $queue = $this;
        try {
            $this->dispatcher->dispatch(BernardEvents::PING, new PingEvent($queue));
            $this->dispatcher->dispatch(BernardEvents::INVOKE, new EnvelopeEvent($envelope, $queue));

            // for 5.3 support where a function name is not a callable
            call_user_func($this->router->map($envelope), $envelope->getMessage());

            // We successfully processed the message.
            $queue->acknowledge($envelope);

            $this->dispatcher->dispatch(BernardEvents::ACKNOWLEDGE, new EnvelopeEvent($envelope, $queue));
        } catch (\Throwable $e) {
            $this->dispatcher->dispatch(BernardEvents::REJECT, new RejectEnvelopeEvent($envelope, $queue, $e));
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        $this->errorIfClosed();

        // Sync queue is always empty
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function peek($index = 0, $limit = 20)
    {
        $this->errorIfClosed();

        // Sync queue is always empty
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $this->errorIfClosed();

        // Sync queue is always empty
        return 0;
    }

}