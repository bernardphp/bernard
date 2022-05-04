<?php

declare(strict_types=1);

namespace Bernard\Driver\MongoDB;

use Bernard\Driver\Message;
use MongoCollection;
use MongoDate;
use MongoId;

final class Driver implements \Bernard\Driver
{
    private MongoCollection $queues;

    private MongoCollection $messages;

    public function __construct(MongoCollection $queues, MongoCollection $messages)
    {
        $this->queues = $queues;
        $this->messages = $messages;
    }

    public function listQueues(): array
    {
        return $this->queues->distinct('_id');
    }

    public function createQueue(string $queueName): void
    {
        $data = ['_id' => $queueName];

        $this->queues->update($data, $data, ['upsert' => true]);
    }

    public function removeQueue(string $queueName): void
    {
        $this->queues->remove(['_id' => $queueName]);
        $this->messages->remove(['queue' => $queueName]);
    }

    public function pushMessage(string $queueName, string $message): void
    {
        $data = [
            'queue' => $queueName,
            'message' => $message,
            'sentAt' => new MongoDate(),
            'visible' => true,
        ];

        $this->messages->insert($data);
    }

    public function popMessage(string $queueName, int $duration = 5): ?Message
    {
        $runtime = microtime(true) + $duration;

        while (microtime(true) < $runtime) {
            $result = $this->messages->findAndModify(
                ['queue' => $queueName, 'visible' => true],
                ['$set' => ['visible' => false]],
                ['message' => 1],
                ['sort' => ['sentAt' => 1]]
            );

            if ($result) {
                return new Message((string) $result['message'], (string) $result['_id']);
            }

            usleep(10000);
        }

        return null;
    }

    public function acknowledgeMessage(string $queueName, mixed $receipt): void
    {
        $this->messages->remove([
            '_id' => new MongoId((string) $receipt),
            'queue' => $queueName,
        ]);
    }

    public function info(): array
    {
        return [
            'queues' => (string) $this->queues,
            'messages' => (string) $this->messages,
        ];
    }

    public function countMessages(string $queueName): int
    {
        return $this->messages->count([
            'queue' => $queueName,
            'visible' => true,
        ]);
    }

    public function peekQueue(string $queueName, int $index = 0, int $limit = 20): array
    {
        $query = ['queue' => $queueName, 'visible' => true];
        $fields = ['_id' => 0, 'message' => 1];

        $cursor = $this->messages
            ->find($query, $fields)
            ->sort(['sentAt' => 1])
            ->limit($limit)
            ->skip($index);

        $mapper = fn ($result) => (string) $result['message'];

        return array_map($mapper, iterator_to_array($cursor, false));
    }
}
