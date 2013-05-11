<?php

namespace Bernard;

/**
 * @package Consumer
 */
class Consumer
{
    protected $services;
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
     * {@inheritDoc}
     */
    public function consume(Queue $queue, Queue $failed = null, array $options = array())
    {
        declare(ticks=1);

        $options = array_merge($this->defaults, array_filter($options));
        $runtime = microtime(true) + $options['max-runtime'];

        pcntl_signal(SIGTERM, array($this, 'trap'), true);
        pcntl_signal(SIGINT, array($this, 'trap'), true);

        while (microtime(true) < $runtime && $envelope = $queue->dequeue() && !$this->shutdown) {
            try {
                $message = $envelope->getMessage();

                $invocator = $this->services->resolve($message);
                $invocator->invoke();
            } catch (\Exception $e) {
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
