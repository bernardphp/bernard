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

    /**
     * @param ServiceResolverInterface $services
     * @param Queue $failed Failed messages will be enqueued on this.
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
        while (true) {
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
    }
}
