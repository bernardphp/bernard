<?php

namespace Bernard\Driver;

use IronMQ;

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
     * @param IronMQ       $ironmq
     * @param integer|null $prefetch
     */
    public function __construct(IronMQ $ironmq, $prefetch = null)
    {
        parent::__construct($prefetch);

        $this->ironmq = $ironmq;
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        if ($info = $this->ironmq->getQueue($queueName)) {
            return $info->size;
        }
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
        if ($message = $this->cache->pop($queueName)) {
            return $message;
        }

        $runtime = microtime(true) + $interval;

        while ($runtime > microtime(true)) {
            $messages = $this->ironmq->getMessages($queueName, $this->prefetch);

            if (!$messages) {
                usleep(10000);
                continue;
            }

            foreach ($messages as $message) {
                $this->cache->push($queueName, array($message->body, $message->id));
            }

            return $this->cache->pop($queueName);
        }

        return array(null, null);
    }

    /**
     * {@inheritDoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->ironmq->deleteMessage($queueName, $receipt);
    }

    /**
     * IronMQ does not support an offset when peeking messages.
     *
     * {@inheritDoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        if ($messages = $this->ironmq->peekMessages($queueName, $limit)) {
            return $this->pluck($messages, 'body');
        }

        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        return array(
            'prefetch' => $this->prefetch,
        );
    }

    /**
     * The missing array_pluck but for objects array
     *
     * @param  array  $objects
     * @param  string $property
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
