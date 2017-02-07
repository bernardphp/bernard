<?php

namespace Bernard\Driver;

use IronMQ\IronMQ;

/**
 * Implements a Driver for use with Iron MQ:
 * http://dev.iron.io/mq/reference/api/
 *
 * @package Bernard
 */
class IronMqDriver extends AbstractPrefetchDriver
{
    protected $ironmq;

    /**
     * @param IronMQ   $ironmq
     * @param int|null $prefetch
     */
    public function __construct(IronMQ $ironmq, $prefetch = null)
    {
        parent::__construct($prefetch);

        $this->ironmq = $ironmq;
    }

    /**
     * {@inheritdoc}
     */
    public function listQueues()
    {
        $queueNames = array();
        $page = 0;

        while ($queues = $this->ironmq->getQueues($page, 100)) {
            $queueNames += $this->pluck($queues, 'name');

            // If we get 100 results the probability of another page is high.
            if (count($queues) < 100) {
                break;
            }

            $page++;
        }

        return $queueNames;
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function countMessages($queueName)
    {
        if ($info = $this->ironmq->getQueue($queueName)) {
            return $info->size;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message)
    {
        $this->ironmq->postMessage($queueName, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        if ($message = $this->cache->pop($queueName)) {
            return $message;
        }

        $timeout = IronMQ::GET_MESSAGE_TIMEOUT;

        $messages = $this->ironmq->getMessages($queueName, $this->prefetch, $timeout, $duration);

        if (!$messages) {
            return array(null, null);
        }

        foreach ($messages as $message) {
            $this->cache->push($queueName, array($message->body, $message->id));
        }

        return $this->cache->pop($queueName);
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->ironmq->deleteMessage($queueName, $receipt);
    }

    /**
     * IronMQ does not support an offset when peeking messages.
     *
     * {@inheritdoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        if ($messages = $this->ironmq->peekMessages($queueName, $limit)) {
            return $this->pluck($messages, 'body');
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function removeQueue($queueName)
    {
        $this->ironmq->deleteQueue($queueName);
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return [
            'prefetch' => $this->prefetch,
        ];
    }

    /**
     * The missing array_pluck but for objects array
     *
     * @param array  $objects
     * @param string $property
     *
     * @return array
     */
    protected function pluck(array $objects, $property)
    {
        $function = function ($object) use ($property) {
            return $object->$property;
        };

        return array_map($function, $objects);
    }
}
