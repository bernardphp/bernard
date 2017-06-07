<?php

namespace Bernard\Middleware;

use Bernard\Message\FailureMessage;
use Bernard\Middleware;
use Bernard\QueueFactory;
use Bernard\Queue;
use Bernard\Envelope;
use Exception;

/**
 * @package Bernard
 */
class RetryMiddleware implements Middleware
{
    protected $queues;
    protected $next;

    /**
     * @param Middleware   $next
     * @param QueueFactory $queues
     */
    public function __construct(Middleware $next, QueueFactory $queues)
    {
        $this->next = $next;
        $this->queues = $queues;
    }

    /**
     * {@inheritDoc}
     */
    public function call(Envelope $envelope, Queue $queue)
    {
        try {
            if ($envelope->getMessage() instanceof FailureMessage) {
                $this->next->call(new Envelope($envelope->getMessage()), $queue);
            } else {
                $this->next->call($envelope, $queue);
            }
        } catch (Exception $e) {
            // Acknowledge are only done when a Envelope is processed with out interruption. but since
            // it is getting requeued we dont want it retried.
            $queue->acknowledge($envelope);

            $previous = $envelope->getMessage();

            $retries = 0;
            if ($previous instanceof FailureMessage) {
                $retries = $previous->getRetries();
            }

            $failureMessage = new FailureMessage($previous, $retries + 1);

            /**
             * @todo Calculate the exponential backoff time by using $failureMessage->getRetries()
             */

            $this->queues->create($failureMessage->getQueue())->enqueue(new Envelope($failureMessage));

            throw $e;
        }
    }
}
