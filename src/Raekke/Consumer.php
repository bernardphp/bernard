<?php

namespace Raekke;

use Raekke\Queue\Queue;
use Raekke\Consumer\Job;

/**
 * @package Consumer
 */
class Consumer implements ConsumerInterface
{
    protected $failed;
    protected $services;
    protected $shutdown = false;
    protected $defaults = array(
        'max_retries' => 5,
    );

    /**
     * @param ServiceResolverInterface $services
     * @param Queue                    $failed   Failed messages will be enqueued on this.
     */
    public function __construct(
        ServiceResolverInterface $services,
        Queue $failed = null
    ) {
        $this->failed = $failed;
        $this->services = $services;
    }

    /**
     * {@inheritDoc}
     */
    public function consume(Queue $queue, array $options = array())
    {
        $options = array_merge($this->defaults, array_filter($options));

        // register with monitoring object class thing

        $this->registerSignalHandlers();

        while (true) {
            if ($this->shutdown) {
                break;
            }

            if (null === $envelope = $queue->dequeue()) {
                continue;
            }

            try {
                $message = $envelope->getMessage();
                $service = $this->services->resolve($message);

                $job = new Job($service, $message);
                $job->invoke();
            } catch (\Exception $e) {
                if ($envelope->getRetries() < $options['max_retries']) {
                    $envelope->incrementRetries();
                    $queue->enqueue($envelope);

                    continue;
                }

                if (!$this->failed) {
                    continue;
                }

                $this->failed->enqueue($envelope);
            }
        }

        // Unregister with consumer monitoring thing
    }

    public function shutdown()
    {
        $this->shutdown = true;
    }

    public function registerSignalHandlers()
    {
        declare(ticks=1);

        pcntl_signal(\SIGTERM, array($this, 'shutdown'));
        pcntl_signal(\SIGINT, array($this, 'shutdown'));
    }
}
