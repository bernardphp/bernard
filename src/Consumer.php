<?php

declare(strict_types=1);

namespace Bernard;

use Bernard\Event\EnvelopeEvent;
use Bernard\Event\PingEvent;
use Bernard\Event\RejectEnvelopeEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Consumer
{
    protected $router;
    protected $dispatcher;
    protected $shutdown = false;
    protected $pause = false;
    protected $configured = false;
    protected $options = [
        'max-runtime' => \PHP_INT_MAX,
        'max-messages' => null,
        'stop-when-empty' => false,
        'stop-on-error' => false,
    ];

    public function __construct(Router $router, EventDispatcherInterface $dispatcher)
    {
        $this->router = $router;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Starts an infinite loop calling Consumer::tick();.
     */
    public function consume(Queue $queue, array $options = []): void
    {
        declare(ticks=1);

        $this->bind();

        while ($this->tick($queue, $options)) {
            // NO op
        }
    }

    /**
     * Returns true do indicate it should be run again or false to indicate
     * it should not be run again.
     *
     * @return bool
     */
    public function tick(Queue $queue, array $options = [])
    {
        $this->configure($options);

        if ($this->shutdown) {
            return false;
        }

        if (microtime(true) > $this->options['max-runtime']) {
            return false;
        }

        if ($this->pause) {
            return true;
        }

        $this->dispatcher->dispatch(BernardEvents::PING, new PingEvent($queue));

        if (!$envelope = $queue->dequeue()) {
            return !$this->options['stop-when-empty'];
        }

        $this->invoke($envelope, $queue);

        if (null === $this->options['max-messages']) {
            return true;
        }

        return (bool) --$this->options['max-messages'];
    }

    /**
     * Mark Consumer as shutdown.
     */
    public function shutdown(): void
    {
        $this->shutdown = true;
    }

    /**
     * Pause consuming.
     */
    public function pause(): void
    {
        $this->pause = true;
    }

    /**
     * Resume consuming.
     */
    public function resume(): void
    {
        $this->pause = false;
    }

    /**
     * Until there is a real extension point to doing invoked stuff, this can be used
     * by wrapping the invoke method.
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function invoke(Envelope $envelope, Queue $queue): void
    {
        try {
            $this->dispatcher->dispatch(BernardEvents::INVOKE, new EnvelopeEvent($envelope, $queue));

            $receiver = $this->router->route($envelope);
            $receiver->receive($envelope->getMessage());

            // We successfully processed the message.
            $queue->acknowledge($envelope);

            $this->dispatcher->dispatch(BernardEvents::ACKNOWLEDGE, new EnvelopeEvent($envelope, $queue));
        } catch (\Throwable $error) {
            $this->rejectDispatch($error, $envelope, $queue);
        } catch (\Exception $exception) {
            $this->rejectDispatch($exception, $envelope, $queue);
        }
    }

    protected function configure(array $options)
    {
        if ($this->configured) {
            return $this->options;
        }

        $this->options = array_filter($options) + $this->options;
        $this->options['max-runtime'] += microtime(true);
        $this->configured = true;
    }

    /**
     * Setup signal handlers for unix signals.
     *
     * If the process control extension does not exist (e.g. on Windows), ignore the signal handlers.
     * The difference is that when terminating the consumer, running processes will not stop gracefully
     * and will terminate immediately.
     */
    protected function bind(): void
    {
        if (\function_exists('pcntl_signal')) {
            pcntl_signal(\SIGTERM, [$this, 'shutdown']);
            pcntl_signal(\SIGINT, [$this, 'shutdown']);
            pcntl_signal(\SIGQUIT, [$this, 'shutdown']);
            pcntl_signal(\SIGUSR2, [$this, 'pause']);
            pcntl_signal(\SIGCONT, [$this, 'resume']);
        }
    }

    /**
     * @param \Throwable|\Exception $exception note that the type-hint is missing due to PHP 5.x compat
     *
     * @throws \Exception
     * @throws \Throwable
     */
    private function rejectDispatch($exception, Envelope $envelope, Queue $queue): void
    {
        // Make sure the exception is not interfering.
        // Previously failing jobs handling have been moved to a middleware.
        //
        // Emit an event to let others log that exception
        $this->dispatcher->dispatch(BernardEvents::REJECT, new RejectEnvelopeEvent($envelope, $queue, $exception));

        if ($this->options['stop-on-error']) {
            throw $exception;
        }
    }
}
