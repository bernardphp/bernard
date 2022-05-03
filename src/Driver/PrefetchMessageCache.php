<?php

declare(strict_types=1);

namespace Bernard\Driver;

/**
 * @internal
 */
final class PrefetchMessageCache
{
    private array $caches = [];

    /**
     * Pushes a $message to the bottom of the cache.
     */
    public function push(string $queueName, \Bernard\DriverMessage $message): void
    {
        $cache = $this->get($queueName);
        $cache->enqueue($message);
    }

    /**
     * Get the next message in line. Or nothing if there is no more
     * in the cache.
     */
    public function pop(string $queueName): ?\Bernard\DriverMessage
    {
        $cache = $this->get($queueName);

        if (!$cache->isEmpty()) {
            return $cache->dequeue();
        }

        return null;
    }

    /**
     * Create the queue cache internally if it doesn't yet exists.
     */
    private function get(string $queueName): \SplQueue
    {
        if (isset($this->caches[$queueName])) {
            return $this->caches[$queueName];
        }

        return $this->caches[$queueName] = new \SplQueue();
    }
}
