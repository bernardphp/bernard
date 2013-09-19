<?php

namespace Bernard\Middleware;

use Bernard\Message\FailedMessage;
use Bernard\Middleware;
use Bernard\QueueFactory;
use Bernard\Queue;
use Bernard\Envelope;
use Bernard\RetryEnvelope;
use Exception;

/**
 * @package Bernard
 */
class RetryMiddleware implements Middleware
{
    protected $queues;
    protected $next;
    protected $name;

    /**
     * @param Middleware   $next
     * @param QueueFactory $queues
     * @param string       $name
     */
    public function __construct(Middleware $next, QueueFactory $queues, $name = 'failed')
    {
        $this->next = $next;
        $this->queues = $queues;
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function call(Envelope $envelope, Queue $queue)
    {
        try {
            $this->next->call($envelope, $queue);
        } catch (Exception $e) {
            // Acknowledge are only done when a Envelope is processed with out interruption. but since
            // it is getting requeued we dont want it retried.
            $queue->acknowledge($envelope);

            $failedMessage = new FailedMessage($envelope->getMessage());

            /**
             * @todo Do something with the $failedMessage->retryCount()
             */

            echo $failedMessage->getRetryCount() . PHP_EOL;

            $envelope = new Envelope($failedMessage);

            $this->queues->create($this->name)->enqueue($envelope);

            throw $e;
        }
    }
}
