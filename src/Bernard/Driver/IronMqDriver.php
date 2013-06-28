<?php

namespace Bernard\Driver;

use IronMQ;

/**
 * Implements a Driver for use with AWS SQS client API: http://docs.aws.amazon.com/aws-sdk-php-2/latest/class-Aws.Sqs.SqsClient.html
 *
 * @package Bernard
 */
class IronMqDriver implements \Bernard\Driver
{
    protected $ironmq;

    /**
     * @param IronMQ $ironmq
     */
    public function __construct(IronMQ $ironmq)
    {
        $this->ironmq = $ironmq;
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        $info = $this->ironmq->getQueue($queueName);
        if ($info) {
            return $info->size;
        }
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName)
    {
        // not needed, auto-created on use
    }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName)
    {
        $this->ironmq->deleteQueue($queueName);
    }

    /**
     * {@inheritDoc}
     */
    public function listQueues()
    {
        $queues = $this->ironmq->getQueues(0, 100);
        if ($queues) {
            return array_map(function ($queue) {
                return $queue->name;
            }, $queues);
        }
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $this->ironmq->postMessage($queueName, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        $message = $this->ironmq->getMessage($queueName, $interval);
        if ($message) {
            return array($message->body, $message->id);
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->ironmq->deleteMessage($queueName, $receipt);
    }

    /**
     * {@inheritDoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        // not supporting index->limit, just 0->limit
        $messages = $this->ironmq->peekMessages($queueName, $limit);
        if ($messages) {
            return array_map(function ($message) {
                return $message->body;
            }, $messages);
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        // info per queue would be possible.. return info about all queues here?
        return null;
    }

}
