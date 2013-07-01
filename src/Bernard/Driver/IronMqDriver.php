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
    const QUEUE_LIST_MAX_PER_PAGE = 100;

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
        $allQueues = $queues = $this->ironmq->getQueues($page = 0, self::QUEUE_LIST_MAX_PER_PAGE);
        while (count($queues) === self::QUEUE_LIST_MAX_PER_PAGE) {
            $queues = $this->ironmq->getQueues(++$page, self::QUEUE_LIST_MAX_PER_PAGE);
            $allQueues = array_merge($allQueues, $queues);
        }
        $queueNames = array();
        foreach ($allQueues as $queue) {
            $queueNames[] = $queue->name;
        }
        return $queueNames;
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
            $peekMessages = array();
            foreach ($messages as $message) {
                $peekMessages[] = $message->body;
            }
            return $peekMessages;
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
