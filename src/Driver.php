<?php

declare(strict_types=1);

namespace Bernard;

/**
 * Driver implements the necessary methods to interact with the underlying message queue system.
 *
 * Driver is considered to be internal, used only by higher level Bernard components.
 */
interface Driver
{
    /**
     * Returns a list of all queue names.
     */
    public function listQueues(): array;

    /**
     * Create a new queue.
     */
    public function createQueue(string $queueName): void;

    /**
     * Remove an existing queue.
     */
    public function removeQueue(string $queueName): void;

    /**
     * Insert a message at the top of the queue.
     */
    public function pushMessage(string $queueName, string $message): void;

    /**
     * Remove the next message from the bottom of the queue.
     *
     * If no message is available wait for $duration seconds.
     */
    public function popMessage(string $queueName, int $duration = 5): ?DriverMessage;

    /**
     * If the driver supports it, this will be called when a message have been successfully processed.
     */
    public function acknowledgeMessage(string $queueName, mixed $receipt): void;

    /**
     * Returns an associative array with driver specific information.
     */
    public function info(): array;

    /**
     * Count the number of messages in queue. This can be a approximately number.
     */
    public function countMessages(string $queueName): int;

    /**
     * Returns a $limit numbers of messages without removing them from the queue.
     */
    public function peekQueue(string $queueName, int $index = 0, int $limit = 20): array;
}
