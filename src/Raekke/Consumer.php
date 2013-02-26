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
    public function __construct(Queue $failed)
    {
        $this->failed = $failed;
    }

    /**
     * {@inheritDoc}
     */
    public function consume(Queue $queue)
    {
        while (true) {
            if (is_null($wrapper = $queue)) {
                usleep(100);
                continue;
            }

            try {
                // Try and invoke the job, if it exists with \ReflectionException the error
                // is most likely that the method cannot be called on the service object.
                // It should then instantly be marked as failed.
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
                    $this->failed->enqueue($wrapper);
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
