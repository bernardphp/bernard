<?php

declare(strict_types=1);

namespace Bernard\Driver\InMemory;

use Bernard\Driver\Message;

/**
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class Driver implements \Bernard\Driver
{
    private array $queues = [];

    public function listQueues(): array
    {
        return array_keys($this->queues);
    }

    public function createQueue(string $queueName): void
    {
        if (!\array_key_exists($queueName, $this->queues)) {
            $this->queues[$queueName] = [];
        }
    }

    public function removeQueue(string $queueName): void
    {
        if (\array_key_exists($queueName, $this->queues)) {
            unset($this->queues[$queueName]);
        }
    }

    public function pushMessage(string $queueName, string $message): void
    {
        $this->queues[$queueName][] = $message;
    }

    public function popMessage(string $queueName, int $duration = 5): ?Message
    {
        if (!\array_key_exists($queueName, $this->queues) || \count($this->queues[$queueName]) < 1) {
            return null;
        }

        return new Message(array_shift($this->queues[$queueName]));
    }

    public function acknowledgeMessage(string $queueName, mixed $receipt): void
    {
        // Noop
    }

    public function info(): array
    {
        return [];
    }

    public function countMessages(string $queueName): int
    {
        if (\array_key_exists($queueName, $this->queues)) {
            return \count($this->queues[$queueName]);
        }

        return 0;
    }

    public function peekQueue(string $queueName, int $index = 0, int $limit = 20): array
    {
        if (\array_key_exists($queueName, $this->queues)) {
            return \array_slice($this->queues[$queueName], $index, $limit);
        }

        return [];
    }
}
