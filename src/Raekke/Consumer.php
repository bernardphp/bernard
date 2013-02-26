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
    public function consume(Queue $queue)
    {
        // register with monitoring object class thing

        $this->registerSignalHandlers();

        while (true) {
            if ($this->shutdown) {
                break;
            }

            if (null === $wrapper = $queue->dequeue()) {
                continue;
            }

            try {
                $message = $wrapper->getMessage();
                $service = $this->services->resolve($message);

                $job = new Job($service, $message);
                $job->invoke();
            } catch (\Exception $e) {
                if (!$this->failed) {
                    continue;
                }

                $this->failed->enqueue($wrapper);
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
        pcntl_signal(\SIGTERM, array($this, 'shutdown'));
        pcntl_signal(\SIGINT, array($this, 'shutdown'));
    }
}
