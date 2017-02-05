<?php

namespace Bernard\Driver;

/**
 * @package Bernard
 */
class PrefetchMessageCache
{
    protected $caches = [];

    /**
     * Pushes a $message to the end of the cache.
     *
     * @param string $queueName
     * @param array  $message
     */
    public function push($queueName, array $message)
    {
        $cache = $this->get($queueName);
        $cache->enqueue($message);
    }

    /**
     * Get the next message in line. Or nothing if there is no more
     * in the cache.
     *
     * @param string $queueName
     *
     * @return array|null
     */
    public function pop($queueName)
    {
        $cache = $this->get($queueName);

        if (!$cache->isEmpty()) {
            return $cache->dequeue();
        }
    }

    /**
     * Create the queue cache internally if it doesn't yet exists.
     *
     * @param string $queueName
     *
     * @return \SplQueue
     */
    protected function get($queueName)
    {
        if (isset($this->caches[$queueName])) {
            return $this->caches[$queueName];
        }

        return $this->caches[$queueName] = new \SplQueue();
    }
}
