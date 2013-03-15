<?php

namespace Bernard;

use Bernard\Consumer\Job;

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
        'max_runtime' => PHP_INT_MAX,
    );

    /**
     * @param ServiceResolver $services
     * @param Queue           $failed
     */
    public function __construct(
        ServiceResolver $services,
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
        declare(ticks=1);

        $options = array_merge($this->defaults, array_filter($options));
        $runtime = microtime(true) + $options['max_runtime'];

        pcntl_signal(SIGTERM, array($this, 'trap'), true);
        pcntl_signal(SIGINT, array($this, 'trap'), true);

        while (microtime(true) < $runtime) {
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
                $job();
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
