<?php

namespace Bernard\Driver\NewMongoDB;

use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectID;

/**
 * Driver supporting MongoDB.
*/
final class Driver implements \Bernard\Driver
{
    private $messages;
    private $queues;

    /**
     * @param MongoCollection $queues   Collection where queues will be stored
     * @param MongoCollection $messages Collection where messages will be stored
     */
    public function __construct(Collection $queues, Collection $messages)
    {
        $this->queues = $queues;
        $this->messages = $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function listQueues()
    {
        return $this->queues->distinct('_id');
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
        $data = ['_id' => (string) $queueName];
        $updateData = ['$set' => $data];

        $this->queues->updateOne($data, $updateData, ['upsert' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function countMessages($queueName)
    {
        return $this->messages->count([
            'queue' => (string) $queueName,
            'visible' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message)
    {
        $data = [
            'queue' => (string) $queueName,
            'message' => (string) $message,
            'sentAt' => new UTCDateTime(),
            'visible' => true,
        ];

        $this->messages->insertOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        $runtime = microtime(true) + $duration;

        while (microtime(true) < $runtime) {
            $result = $this->messages->findOneAndUpdate(
                ['queue' => (string) $queueName, 'visible' => true],
                ['$set' => ['visible' => false]],
                ['sort' => ['sentAt' => 1], 'projection' => ['message' => 1]]
            );

            if ($result) {
                return [(string) $result['message'], (string) $result['_id']];
            }

            usleep(10000);
        }

        return [null, null];
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->messages->deleteOne([
            '_id' => new ObjectId((string) $receipt),
            'queue' => (string) $queueName,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        $query = ['queue' => (string) $queueName, 'visible' => true];
        $fields = ['_id' => 0, 'message' => 1];

        $results = $this->messages
            ->find(
                $query,
                [
                    'projection' => $fields,
                    'sort' => ['sentAt' => 1],
                    'limit' => $limit,
                    'skip' => $index
                ]
            )
            ->toArray();

        return array_map(function($result){return $result['message']; }, $results);
    }

    /**
     * {@inheritdoc}
     */
    public function removeQueue($queueName)
    {
        $this->queues->deleteOne(['_id' => $queueName]);
        $this->messages->deleteMany(['queue' => (string) $queueName]);
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return [
            'messages' => (string) $this->messages,
            'queues' => (string) $this->queues,
        ];
    }
}
