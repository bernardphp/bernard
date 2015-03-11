<?php

namespace Bernard\Driver;

use MongoCollection;
use MongoDate;
use MongoId;

/**
 * Driver supporting MongoDB
 *
 * @package Bernard
 */
class MongoDBDriver implements \Bernard\Driver
{
    private $messages;
    private $queues;

    /**
     * @param MongoCollection $queues   Collection where queues will be stored
     * @param MongoCollection $messages Collection where messages will be stored
     */
    public function __construct(MongoCollection $queues, MongoCollection $messages)
    {
        $this->queues = $queues;
        $this->messages = $messages;
    }

    /**
     * {@inheritDoc}
     */
    public function listQueues()
    {
        return $this->queues->distinct('_id');
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName)
    {
        $data = ['_id' => (string) $queueName];

        $this->queues->update($data, $data, ['upsert' => true]);
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        return $this->messages->count([
            'queue' => (string) $queueName,
            'visible' => true,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $data = [
            'queue'   => (string) $queueName,
            'message' => (string) $message,
            'sentAt'  => new MongoDate(),
            'visible' => true,
        ];

        $this->messages->insert($data);
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        $runtime = microtime(true) + $interval;

        while (microtime(true) < $runtime) {
            $result = $this->messages->findAndModify(
                ['queue' => (string) $queueName, 'visible' => true],
                ['$set' => ['visible' => false]],
                ['message' => 1],
                ['sort' => ['sentAt' => 1]]
            );

            if ($result) {
                return [(string) $result['message'], (string) $result['_id']];
            }

            usleep(10000);
        }

        return [null, null];
    }

    /**
     * {@inheritDoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->messages->remove([
            '_id' => new MongoId((string) $receipt),
            'queue' => (string) $queueName,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $query = ['queue' => (string) $queueName, 'visible' => true];
        $fields = ['_id' => 0, 'message' => 1];

        $cursor = $this->messages
            ->find($query, $fields)
            ->sort(['sentAt' => 1])
            ->limit($limit)
            ->skip($index)
        ;

        $mapper = function ($result) {
            return (string) $result['message'];
        };

        return array_map($mapper, iterator_to_array($cursor, false));
    }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName)
    {
        $this->queues->remove(['_id' => $queueName]);
        $this->messages->remove(['queue' => (string) $queueName]);
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        return [
            'messages' => (string) $this->messages,
            'queues' => (string) $this->queues,
        ];
    }
}
