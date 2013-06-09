<?php

namespace Bernard;

use Bernard\EventDispatcher\MessageEvent;
use Bernard\EventDispatcher\ExceptionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @package Consumer
 */
class Consumer
{
    protected $services;
    protected $dispatcher;
    protected $shutdown = false;
    protected $defaults = array(
        'max-retries' => 5,
        'max-runtime' => PHP_INT_MAX,
    );

    /**
     * @param ServiceResolver $services
     */
    public function __construct(ServiceResolver $services)
    {
        $this->services = $services;
    }

    /**
     * Set the event dispatcher to dispatch PRODUCE and EXCEPTION events.
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
    public function consume(Queue $queue, Queue $failed = null, array $options = array())
    {
        declare(ticks=1);

        $options = array_merge($this->defaults, array_filter($options));
        $runtime = microtime(true) + $options['max-runtime'];

        pcntl_signal(SIGTERM, array($this, 'trap'), true);
        pcntl_signal(SIGINT, array($this, 'trap'), true);

        while (microtime(true) < $runtime && !$this->shutdown) {
            if (!$envelope = $queue->dequeue()) {
                continue;
            }

            try {
                $message = $envelope->getMessage();

                $invocator = $this->services->resolve($message);
                $invocator->invoke();

                if (null !== $this->dispatcher) {
                    $this->dispatcher->dispatch(BernardEvents::CONSUME, new MessageEvent($message));
                }
            } catch (\Exception $exception) {
                if (null !== $this->dispatcher) {
                    $this->dispatcher->dispatch(BernardEvents::EXCEPTION, new ExceptionEvent($message, $exception));
                }

                if ($envelope->getRetries() < $options['max-retries']) {
                    $envelope->incrementRetries();
                    $queue->enqueue($envelope);

                    continue;
                }

                if ($failed) {
                    $failed->enqueue($envelope);
                }
            }
        }
    }

    /**
     * Mark consumer as terminating
     *
     * @param integer $signal
     */
    public function trap($signal)
    {
        $this->shutdown = true;
    }
}
