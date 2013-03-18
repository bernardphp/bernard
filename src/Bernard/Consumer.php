<?php

namespace Bernard;

use Bernard\Consumer\Job;
use Psr\Log\LoggerInterface as Logger;
use Psr\Log\NullLogger;

/**
 * @package Consumer
 */
class Consumer implements ConsumerInterface
{
    protected $logger;
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
    public function __construct(ServiceResolver $services, Logger $logger = null)
    {
        $this->services = $services;
        $this->logger   = $logger ?: new NullLogger;
    }

    /**
     * {@inheritDoc}
     */
    public function consume(Queue $queue, Queue $failed = null, array $options = array())
    {
        declare(ticks=1);

        $options = array_merge($this->defaults, array_filter($options));
        $runtime = microtime(true) + $options['max_runtime'];

        pcntl_signal(SIGTERM, array($this, 'trap'), true);
        pcntl_signal(SIGINT, array($this, 'trap'), true);

        $this->logger->debug('bernard: Starting "{id}" on "{queue}".', array(
            'id' => (string) $this,
            'queue' => $queue->getName(),
        ));

        while (microtime(true) < $runtime) {
            if ($this->shutdown) {
                break;
            }

            if (null === $envelope = $queue->dequeue()) {
                $this->logger->debug('bernard: Timed out waiting for message.');

                continue;
            }

            $this->logger->info('bernard: Recieved message with "{message}"', array(
                'message' => json_encode($envelope),
            ));

            try {
                $message = $envelope->getMessage();
                $service = $this->services->resolve($message);

                $job = new Job($service, $message);
                $job->invoke();

                $this->logger->debug('bernard: Finished processing message.');
            } catch (\Exception $e) {
                $this->logger->info('bernard: Failed processing "{message}" with "{service}".', array(
                    'message' => json_encode($enveloope),
                    'service' => (string) $job,
                ));

                if ($envelope->getRetries() < $options['max_retries']) {
                    $this->logger->debug('bernard: Retrying message {retries} of {max_retries}.', array(
                        'retries' => $envelope->getRetries(),
                        'mex_retries' => $options['max_retries'],
                    ));

                    $envelope->incrementRetries();
                    $queue->enqueue($envelope);

                    continue;
                }

                if (!$failed) {
                    $this->logger->debug('bernard: No failed queue message will vanish.');

                    continue;
                }

                $this->logger->debug('bernard: Message failed, moving to "{queue}".', array(
                    'queue' => $failed->getName(),
                ));

                $failed->enqueue($envelope);
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

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
       return sprintf('%s:%s', gethostname(), getmypid());
    }
}
