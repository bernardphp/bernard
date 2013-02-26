<?php

namespace Raekke;

use Raekke\Queue\Queue;
use Raekke\Consumer\Job;
use Raekke\Message\MessageInterface;

/**
 * @package Consumer
 */
class Consumer implements ConsumerInterface
{
    protected $failed;

    /**
     * @param Queue $failed Failed messages will be enqueued on this.
     */
    public function __construct(Queue $failed = null)
    {
        $this->failed = $failed;
    }

    /**
     * {@inheritDoc}
     */
    public function consume(Queue $queue)
    {
        while (true) {
            if (null === $wrapper = $queue) {
                continue;
            }

            try {
                $job = new Job(new ServiceObject, $wrapper->getMessage());
                $job->invoke();
            } catch (\Exception $e) {
                $failed = false;

                if ($e instanceof \ReflectionException) {
                    $failed = true;
                }

                if ($wrapper->getRetries() < 5) {
                    $failed = true;
                }

                if ($failed) {
                    if ($this->failed) {
                        $this->failed->enqueue($wrapper);
                    }

                    continue;
                }

                // Increment retries and requeue
                $wrapper->incrementRetries();
                $queue->enqueue($wrapper);
            }
        }
    }
}

class ServiceObject
{
    public function onSendNewsletter(MessageInterface $message)
    {
        sleep(10);
    }
}
